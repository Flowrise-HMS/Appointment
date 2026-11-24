<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentRecurrenceRule;
use Modules\Appointment\Models\AppointmentSchedule;
use Modules\Appointment\Models\AppointmentSlot;
use Modules\Appointment\Models\ScheduleBlock;
use Modules\Core\Models\Branch;
use Modules\Patient\Models\Patient;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\ApiResourceTestSuite;
use Tests\TestCase;

uses(TestCase::class, DatabaseTransactions::class);

$modules = ['Core', 'Patient', 'Staff', 'Appointment'];

function appointmentApiAppointment(Branch $branch): Appointment
{
    $patient = Patient::withoutEvents(fn () => Patient::factory()->create(['branch_id' => $branch->id]));
    $start = now()->addDays(2)->setTime(9, 0);

    return Appointment::factory()->create([
        'branch_id' => $branch->id,
        'patient_id' => $patient->id,
        'practitioner_primary_id' => null,
        'status' => AppointmentStatus::BOOKED,
        'start_at' => $start,
        'end_at' => $start->copy()->addMinutes(30),
    ]);
}

describe('schedules', function () use ($modules): void {
    ApiResourceTestSuite::register([
        'uri' => 'schedules',
        'subject' => 'AppointmentSchedule',
        'scope' => 'appointments',
        'modules' => $modules,
        'make' => fn (TestCase $test, Branch $branch, array $attributes = []) => AppointmentSchedule::factory()->create(['branch_id' => $branch->id, ...$attributes]),
        'store' => fn (TestCase $test, Branch $branch): array => [
            'name' => 'OPD mornings',
            'slot_minutes' => 30,
            'timezone' => 'UTC',
            'availabilities' => [['weekday' => 1, 'start_time' => '09:00', 'end_time' => '11:00']],
        ],
        'invalid' => [['name' => 'Bad', 'slot_minutes' => 1], 'slot_minutes'],
        'update' => fn (TestCase $test, $record): array => ['comment' => 'Room 4'],
        'destroy' => true,
    ]);
});

describe('slots', function () use ($modules): void {
    ApiResourceTestSuite::register([
        'uri' => 'slots',
        // Slot access follows schedule permissions (AppointmentSlotPolicy).
        'subject' => 'AppointmentSchedule',
        'scope' => 'appointments',
        'modules' => $modules,
        'make' => function (TestCase $test, Branch $branch, array $attributes = []) {
            $schedule = AppointmentSchedule::factory()->create(['branch_id' => $branch->id]);

            return AppointmentSlot::factory()->create(['schedule_id' => $schedule->id, 'branch_id' => $branch->id, ...$attributes]);
        },
    ]);
});

describe('recurrence rules', function () use ($modules): void {
    ApiResourceTestSuite::register([
        'uri' => 'recurrence-rules',
        'subject' => 'AppointmentRecurrenceRule',
        'scope' => 'appointments',
        'modules' => $modules,
        'make' => fn (TestCase $test, Branch $branch, array $attributes = []) => AppointmentRecurrenceRule::factory()->create(['appointment_id' => appointmentApiAppointment($branch)->id, 'branch_id' => $branch->id, 'occurrence_count' => 1, ...$attributes]),
        'store' => fn (TestCase $test, Branch $branch): array => ['appointment_id' => appointmentApiAppointment($branch)->id, 'frequency' => 'weekly', 'occurrence_count' => 2],
        'invalid' => [['frequency' => 'weekly'], 'occurrence_count'],
    ]);
});

describe('schedule blocks', function () use ($modules): void {
    ApiResourceTestSuite::register([
        'uri' => 'schedule-blocks',
        'subject' => 'ScheduleBlock',
        'scope' => 'appointments',
        'modules' => $modules,
        'make' => fn (TestCase $test, Branch $branch, array $attributes = []) => ScheduleBlock::factory()->create(['branch_id' => $branch->id, ...$attributes]),
        'store' => fn (TestCase $test, Branch $branch): array => ['practitioner_id' => (string) Str::uuid(), 'reason' => 'Leave', 'blocked_from' => now()->addDay()->toIso8601String(), 'blocked_to' => now()->addDays(3)->toIso8601String()],
        'invalid' => [['blocked_from' => now()->addDays(3)->toIso8601String(), 'blocked_to' => now()->addDay()->toIso8601String()], 'blocked_to'],
        'destroy' => true,
    ]);
});

describe('scheduling workflow', function () use ($modules): void {
    beforeEach(function () use ($modules): void {
        $this->requireModule('Api');
        $this->migrateModules($modules);
        Carbon::setTestNow(Carbon::parse('next monday 07:00', 'UTC'));
        $this->branch = Branch::factory()->create(['timezone' => 'UTC']);
        $this->user = User::factory()->create(['branch_id' => $this->branch->id]);

        foreach (['AppointmentSchedule', 'AppointmentRecurrenceRule'] as $subject) {
            foreach (['ViewAny', 'View', 'Create', 'Update'] as $action) {
                Permission::findOrCreate("{$action} {$subject}", 'web');
                $this->user->givePermissionTo("{$action} {$subject}");
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->withToken($this->user->createToken('t', ['appointments:write'])->plainTextToken);
    });

    afterEach(fn () => Carbon::setTestNow());

    it('creates a schedule whose free slots can be listed', function (): void {
        $practitionerId = (string) Str::uuid();

        $this->postJson('/api/v1/schedules', [
            'name' => 'Monday clinic',
            'practitioner_id' => $practitionerId,
            'slot_minutes' => 30,
            'generation_days' => 7,
            'timezone' => 'UTC',
            'availabilities' => [['weekday' => 1, 'start_time' => '09:00', 'end_time' => '10:00']],
        ])->assertCreated()->assertJsonPath('data.availabilities.0.start_time', '09:00');

        $this->getJson("/api/v1/slots?status=free&practitioner_id={$practitionerId}")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('books recurrence occurrences and reports them', function (): void {
        $appointment = appointmentApiAppointment($this->branch);

        $id = $this->postJson('/api/v1/recurrence-rules', ['appointment_id' => $appointment->id, 'frequency' => 'weekly', 'occurrence_count' => 3])
            ->assertCreated()
            ->assertJsonCount(3, 'data.last_expansion.created')
            ->json('data.id');

        $this->postJson("/api/v1/recurrence-rules/{$id}/generate")->assertOk()->assertJsonCount(0, 'data.last_expansion.created')->assertJsonPath('data.generated_count', 3);
        $this->postJson("/api/v1/recurrence-rules/{$id}/cancel-series")->assertOk()->assertJsonPath('data.is_active', false);
    });
});
