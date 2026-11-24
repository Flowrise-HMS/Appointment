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
| `AppointmentRecurrenceRule` | `appointment_recurrence_rules` | Recurrence definition (frequency, interval, `by_day`, occurrence count / until, timezone, active). `RecurrenceExpansionService` books the occurrences: the Filament create/edit pages and relation manager expand on save, and the MCH module calls it for ANC returns. The rule inherits the parent's branch and, if unset, the branch timezone. |
| `AppointmentAudit` | `appointment_audits` | Optional domain audit rows (manual / Filament); not auto-written by scheduling. |
| `ScheduleBlock` | `appointment_schedule_blocks` | Practitioner/location blocks for conflict detection. |
| `AppointmentSchedule` | `appointment_schedules` | Availability for a practitioner (and/or service, location, department): slot length, validity window (planning horizon), rolling generation window, timezone. FHIR Schedule-shaped; no FHIR endpoint yet. |
| `ScheduleAvailability` | `appointment_schedule_availabilities` | Weekly hours on a schedule (ISO weekday, local start/end time). |
| `AppointmentSlot` | `appointment_slots` | Bookable slot generated from a schedule; status `free` / `busy` / `blocked`, linked appointment. FHIR Slot-shaped. |
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

`RecurrenceExpansionService::expand(AppointmentRecurrenceRule $rule, array $overrides = [])` creates one `Appointment` per occurrence of an active rule; `expandWithReport()` returns a `Classes\Data\RecurrenceExpansionResult` (created appointments, dates already booked, dates skipped for practitioner conflicts).

- The parent appointment is the first occurrence; `occurrence_count` is the number of **additional** occurrences. Expansion stops at whichever of `occurrence_count` and `until_at` comes first (hard cap 500).
- Dates are computed in the rule's `timezone`, so the local time of day survives DST changes; weekly rules honour `by_day` (`MON`..`SUN`, array or comma string) and otherwise repeat on the parent's weekday; monthly rules never overflow short months.
- Expansion is idempotent: each instance carries `external_reference = recur:{ruleId}:{localDate}`. `cancelSeries()` deactivates the rule and cancels its future booked/arrived instances.

Callers: the MCH module's `AncReturnScheduler` (weekly rule every 4 weeks, 8 occurrences by default) and the Filament UI. Creating a rule on the **Recurrence rules** resource or the appointment's **Recurrence rules** relation manager ("Repeat appointment") books the occurrences and shows a summary notification; saving an edit books any newly covered dates. Table and view actions **Generate occurrences** (idempotent re-run) and **Cancel series** are available to users who can update the rule. The "Recurring appointments" setting hides the relation manager and blocks the resource (`canAccess()`).

### Conflict checks and the Filament forms

`AppointmentSchedulingService` asserts practitioner conflicts (overlapping non-cancelled appointments and schedule blocks) before saving and throws `Exceptions\AppointmentConflictException` (an HTTP 422 for API callers). Every entry point uses it: the REST API, the Clinical workspace quick actions, the discharge follow-up listener, the MCH EPI scheduler and the Filament **Appointments** create/edit pages, relation managers and table edit action (`Classes\Support\AppointmentFormWriter`, which turns a conflict into a persistent **Scheduling conflict** notification and halts the save).

### Schedules and slots

- `SlotGenerationService::generate()` materialises slots from a schedule's weekly hours over `generation_days` from today (clipped to the planning horizon), in the schedule's timezone (schedule → branch → app). It is idempotent: missing slots are created, free future slots that no longer fit the hours are removed, booked/blocked slots are never deleted. `appointments:generate-slots` (daily 01:00, `configureSchedules()`) runs it for every active schedule; the Filament create/edit pages and the **Generate slots** action run it immediately.
- `SlotStatusService` recomputes a slot's status from what overlaps it: blocked by a schedule block for the practitioner (or a practitioner-less block that is branch-wide or on the schedule's location), busy when a live appointment for the practitioner (or, for practitioner-less schedules, the service) overlaps, free otherwise. `Observers\AppointmentSlotSyncObserver` runs it on every Appointment and ScheduleBlock save/delete, so slots stay correct whichever path wrote the booking.
- `ScheduleCoverageService` (called from `AppointmentSchedulingService`): when the practitioner has an active schedule covering the date and the **Enforce practitioner schedules** setting is on, the booking must lie entirely within that practitioner's non-blocked slots, otherwise `Exceptions\OutsideScheduleException` (a subclass of `AppointmentConflictException`, so 422 / Filament conflict notification) is thrown. Days beyond the rolling window are generated on demand. Practitioners without a schedule are unaffected. Recurrence expansion creates instances directly and is checked only for appointment conflicts.
- Website online booking (`Modules/Website` `BookingSlotGenerator`) offers the free slots of active schedules for the requested service at the booking branch and books them with the slot's practitioner; services without a schedule keep the opening-hours slot logic.
- The appointment form shows **Pick a free slot** when the chosen practitioner has upcoming free slots; picking one fills the time window.

### Settings page

`Pages/ManageAppointmentSettings` ("Appointments", Settings group; permission `manage_appointment_settings`) stores `AppointmentSettings` (default duration 5 min, default status booked, default type outpatient, calendar first day of week, waitlist / telehealth / recurrence / external sync / waitlist API toggles, enforce practitioner schedules). All of them are enforced: the defaults seed the appointment form (end time = start + default duration; the Virtual type is offered only when telehealth is enabled), the calendars start the week on the configured day, `external_sync_enabled` gates the sync outbox, `waitlist_enabled` hides the Waitlist resource and (with `waitlist_api_enabled`) the `/api/v1/waitlist` routes (404 when off), `recurrence_enabled` hides the Recurrence Rules relation manager and blocks that resource, and `enforce_schedules` restricts practitioners with an active schedule to their slots.

### Appointment audits vs activity log

- **Spatie activity log** runs on all `BaseModel` children (configurable per model).
- **`AppointmentAudit`** is **not** populated automatically by scheduling today — use Filament for explicit audit rows or add observers later.

## Filament UI

- Cluster **Appointments** in the **Patient Care** sidebar group (`/appointment`, opens on the Calendar): **Calendar** page (Calendar + Schedule Blocks widgets; header **New Schedule Block** for any user with the create permission, practitioner pre-filled from the signed-in staff profile; users without a staff profile see every block in the current branch), Appointments (header **Calendar** link to the Calendar page, inline Check in / Cancel, Bulk Cancel, super-admin **Export appointments**; relation managers Participants, Appointment Audits, Recurrence Rules, Resources), **Schedules** (weekly hours repeater, Generate slots action, read-only Slots relation manager), Schedule Blocks, Waitlist Entries, **Manage Appointment Settings** page. Audits, Participants, Recurrence rules and Sync outbox resources exist but are hidden from navigation (open via URL `/appointment/appointment-audits`, `/appointment/appointment-participants`, `/appointment/appointment-recurrence-rules`, `/appointment/appointment-sync-outboxes`; all but the outbox still expose a Create page).
- Injected into Clinical pages (`Classes/Actions/ClinicalActions`): **Schedule appointment** (full form) and **Add appointment** (quick slide-over: patient, branch, schedule, required reason) on the Clinical Workspace, Patient Workspace, Timeline and Patient Profile pages (**Add appointment** shows on the workspace home without a patient, **Schedule appointment** once a patient is selected); **Check in** on the workspace "Today's appointments" widget, which also marks the patient's planned encounter as arrived.
- Relation managers contributed to other modules: `PatientAppointmentsRelationManager` (patient record), `StaffAppointmentsRelationManager` (staff record).
- `Listeners/CreateFollowUpAppointmentOnDischarge` books a follow-up when a discharge specifies a follow-up date (duration `appointment.follow_up_duration_minutes`, 20).

## REST API (when the Api module is enabled)

`appointments` (with `check-in`, `cancel`, `bulk-reschedule`), `waitlist` (`offer-slot`), `schedules` (`generate-slots`), `slots`, `recurrence-rules` (`generate`, `cancel-series`), `schedule-blocks` under `/api/v1`, token scope `appointments`. FHIR `Appointment` and `AppointmentResponse` are read/search through the FHIR module.

## Operator notes

- Maintain **schedule blocks** for clinicians/locations that must not receive bookings.
- Attach **resources** when rooms/assets must participate in conflict detection.
- Monitor **sync outbox** for stuck `failed` / high `attempts` rows after real integrations ship.

## Developer commands

```bash
php artisan appointment:process-sync-outbox --limit=50
php artisan appointments:generate-slots [--schedule=<uuid>]
```

## Tests

```bash
php artisan test --compact Modules/Appointment/tests   # 28 test files
```

11 migrations, 11 factories. Verified against code on 2026-11-24.
