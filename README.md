# Appointment module

Scheduling, waitlist, outbound sync outbox, and Filament admin surfaces for FlowRise HMS.

## Operator documentation (staff-facing)

End-user workflows for reception, clinical staff, and administrators: **[docs/user-guide/appointments.md](../../docs/user-guide/appointments.md)**.

## Branch context (required reading)

All domain models extend [`Modules\Core\Models\BaseModel`](../Core/app/Models/BaseModel.php), which applies [`BelongsToBranch`](../Core/app/Traits/BelongsToBranch.php):

- **Global scope**: queries default to `branch_id = current_branch_id` from Laravel `Context` or `Auth::user()->branch_id`.
- **Creating**: `branch_id` is auto-filled from that context when absent.

Console commands, queues, and tests must **set branch context** or call `Model::withoutGlobalScopes()` when operating across branches.

## Entity overview

| Model | Table | Purpose |
|-------|-------|---------|
| `Appointment` | `appointments` | Core booked encounter shell (patient, location, status, times). |
| `AppointmentParticipant` | `appointment_participants` | FHIR-style participants (`participant_type`, `actor_reference` string). |
| `AppointmentResource` | `appointment_resources` | Room/equipment allocations with time windows; feeds conflict checks. |
| `AppointmentRecurrenceRule` | `appointment_recurrence_rules` | Recurrence definition. `RecurrenceExpansionService::expand()` creates the occurrences; today only the MCH module calls it (ANC return dates). Rules entered through the Filament relation manager are stored but not expanded automatically. |
| `AppointmentAudit` | `appointment_audits` | Optional domain audit rows (manual / Filament); not auto-written by scheduling. |
| `ScheduleBlock` | `appointment_schedule_blocks` | Practitioner/location blocks for conflict detection. |
| `WaitlistEntry` | `appointment_waitlist_entries` | Prioritized wait queue per branch/patient preferences. |
| `AppointmentSyncOutbox` | `appointment_sync_outbox` | Reliable outbound events for integrations. |

## Relationships quick reference

### `Appointment`

- `patient`, `location`, `department`, `branch` (explicit `branch`; matches trait).
- `primaryPractitioner` → `Staff` via **`practitioner_primary_id`** (UUID, **no FK** in DB).
- `creator` / `updater` → `App\Models\User` via `created_by` / `updated_by`.
- Children: `participants`, `resources`, `recurrenceRules`, `appointmentAudits`, **`syncOutboxEntries`** (filtered to `aggregate_type = appointment`).

### `AppointmentSyncOutbox`

- `branch()` from trait; **`appointment()`** → `Appointment` on **`aggregate_id`** when `aggregate_type` is `appointment` (`AppointmentSyncOutbox::AGGREGATE_TYPE_APPOINTMENT`).
- Query helpers: `scopeForAppointmentAggregate()`, `scopeDue()` (pending + `available_at` elapsed).

### `WaitlistEntry`

- `patient`, `preferredPractitioner` (Staff, nullable UUID **no FK**), **`preferredLocation`**, `preferredDepartment`, `branch` (trait).

### `AppointmentAudit`

- `appointment`, `branch` (trait), **`actor`** → `User` when `actor_id` points at a web user.

### `ScheduleBlock`

- `branch` (trait), `practitioner` → Staff, `location`, `department`.

### Child rows (`Participant`, `Resource`, `RecurrenceRule`)

- `appointment`, `branch` (trait).

## Soft UUID references

These columns are **strings without FK** until interoperability stabilizes:

- `appointments.practitioner_primary_id`
- `appointment_participants.actor_reference`
- `appointment_schedule_blocks.practitioner_id` (nullable)
- `appointment_waitlist_entries.preferred_practitioner_id`

Prefer Staff UUIDs where `Staff` exists; document external FHIR ids separately if needed.

## Services and workflows

### `AppointmentSchedulingService`

- **schedule** / **reschedule** / **checkIn** / **cancel** mutate `Appointment` and enqueue **`AppointmentSyncOutbox`** rows.
- **Practitioner conflicts**: [`AppointmentConflictService`](app/Classes/Services/AppointmentConflictService.php) checks overlapping `Appointment` rows and `ScheduleBlock`.
- **Resource conflicts**: `hasResourceConflict` when `AppointmentResource` rows exist.

### Outbox producer

Events emitted today:

| `event_name` | When |
|--------------|------|
| `appointment.booked` | After create |
| `appointment.rescheduled` | After reschedule (+ version bump) |
| `appointment.checked_in` | After check-in |
| `appointment.cancelled` | After cancel |

**Idempotency**: `idempotency_key` is `sha256("{appointment_id}|{event_name}|{version}")`. `AppointmentSyncOutbox::firstOrCreate` skips duplicates for the same triple.

**Payload** (JSON): `appointment_id`, `status`, ISO8601 `start_at`, `end_at`.

### Outbox consumer (stub)

Artisan command **`appointment:process-sync-outbox`** (`Modules\Appointment\Console\Commands\ProcessAppointmentSyncOutboxCommand`) selects **due** pending rows (respects `withoutGlobalScopes`) and marks them **`completed`** — replace internals with real HTTP / message-bus dispatch.

Scheduler entry lives in `AppointmentServiceProvider::configureSchedules()` (not in the host `routes/console.php`):

```php
$schedule->command('appointment:process-sync-outbox')->everyMinute();
```

Ensure host cron runs `php artisan schedule:run` every minute in production.

Filament: **no manual create** route for outbox rows — entries are system-generated; operators may **view/edit** for troubleshooting.

### Waitlist scoring

[`WaitlistScoringService`](app/Classes/Services/WaitlistScoringService.php) returns a numeric score only; persist `computed_priority_score` from Filament or a future job.

### Recurrence rules

`RecurrenceExpansionService::expand(AppointmentRecurrenceRule $rule, array $overrides = [])` creates one `Appointment` per occurrence of an active rule (frequency, interval, occurrence count). It is used by the MCH module's `AncReturnScheduler` (weekly rule every 4 weeks, 8 occurrences by default). Rules created through the **Recurrence rules** relation manager on an appointment are stored only; nothing expands them automatically and the "Recurring appointments" toggle on the settings page is not consulted.

### Conflict checks and the Filament forms

`AppointmentSchedulingService` (used by the Clinical workspace quick actions, the REST API and the discharge follow-up listener) asserts practitioner and resource conflicts before saving. The Filament **Appointments** create/edit pages write the model directly and do **not** run these checks, so overlapping bookings can be saved from the list screens.

### Settings page

`Pages/ManageAppointmentSettings` ("Appointments", Settings group; permission `manage_appointment_settings`) stores `AppointmentSettings` (default duration 5 min, default status booked, default type outpatient, calendar first day of week, waitlist / telehealth / recurrence / external sync / waitlist API toggles). None of these values are read by the module at runtime yet; they are stored for future use.

### Appointment audits vs activity log

- **Spatie activity log** runs on all `BaseModel` children (configurable per model).
- **`AppointmentAudit`** is **not** populated automatically by scheduling today — use Filament for explicit audit rows or add observers later.

## Filament UI

- Cluster **Appointments** in the **Patient Care** sidebar group (`/appointment`, opens on the Calendar): **Calendar** page (Calendar + Schedule Blocks widgets), Appointments (table/calendar toggle - the calendar mode rendered an empty table in the 2026-09-20 UI check -, inline Check in / Cancel, Bulk Cancel, super-admin **Export appointments**; relation managers Participants, Appointment Audits, Recurrence Rules, Resources), Schedule Blocks, Waitlist Entries, **Manage Appointment Settings** page. Audits, Participants, Recurrence rules and Sync outbox resources exist but are hidden from navigation (open via URL `/appointment/appointment-audits`, `/appointment/appointment-participants`, `/appointment/appointment-recurrence-rules`, `/appointment/appointment-sync-outboxes`; all but the outbox still expose a Create page).
- Injected into Clinical pages (`Classes/Actions/ClinicalActions`): **Schedule appointment** (full form) and **Add appointment** (quick slide-over: patient, branch, schedule, required reason) on the Clinical Workspace, Patient Workspace, Timeline and Patient Profile pages - only Schedule appointment rendered in the 2026-09-20 UI check; **Check in** on the workspace "Todays appointments" widget.
- Relation managers contributed to other modules: `PatientAppointmentsRelationManager` (patient record), `StaffAppointmentsRelationManager` (staff record).
- `Listeners/CreateFollowUpAppointmentOnDischarge` books a follow-up when a discharge specifies a follow-up date (duration `appointment.follow_up_duration_minutes`, 20).

## REST API (when the Api module is enabled)

`/api/v1/appointments` (apiResource), `POST /api/v1/appointments/{id}/check-in`, `POST /api/v1/appointments/{id}/cancel`, `POST /api/v1/appointments/bulk-reschedule`, `GET/POST /api/v1/waitlist`, `POST /api/v1/waitlist/{id}/offer-slot` (Sanctum + `api.branch`). FHIR `Appointment` and `AppointmentResponse` are read/search through the FHIR module.

## Operator notes

- Maintain **schedule blocks** for clinicians/locations that must not receive bookings.
- Attach **resources** when rooms/assets must participate in conflict detection.
- Monitor **sync outbox** for stuck `failed` / high `attempts` rows after real integrations ship.

## Developer commands

```bash
php artisan appointment:process-sync-outbox --limit=50
```

## Tests

```bash
php artisan test --compact Modules/Appointment/tests   # 13 test files
```

10 migrations, 8 factories, 7 policies. Verified against code on 2026-09-20.
