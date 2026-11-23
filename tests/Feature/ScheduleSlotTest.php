<?php

namespace Modules\Appointment\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\Appointment\Classes\Services\AppointmentSchedulingService;
use Modules\Appointment\Classes\Services\SlotGenerationService;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\SlotStatus;
use Modules\Appointment\Exceptions\AppointmentConflictException;
use Modules\Appointment\Exceptions\OutsideScheduleException;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentSchedule;
use Modules\Appointment\Models\AppointmentSlot;
use Modules\Appointment\Models\ScheduleBlock;
use Modules\Appointment\Settings\AppointmentSettings;
use Modules\Core\Models\Branch;
use Modules\Patient\Models\Patient;
use Tests\TestCase;

class ScheduleSlotTest extends TestCase
{
    use DatabaseTransactions;

    private Branch $branch;

    private Patient $patient;

    private string $practitionerId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateModules(['Core', 'Patient', 'Appointment']);
        Carbon::setTestNow(Carbon::parse('next monday 07:00', 'UTC'));
        $this->branch = Branch::factory()->create(['timezone' => 'UTC']);
        $this->patient = Patient::factory()->create(['branch_id' => $this->branch->id]);
        $this->practitionerId = (string) Str::uuid();
        AppointmentSettings::fake(['enforce_schedules' => true, 'external_sync_enabled' => false]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function schedule(array $attributes = []): AppointmentSchedule
    {
        return AppointmentSchedule::factory()->weekdays('09:00', '11:00')->create(array_merge([
            'branch_id' => $this->branch->id,
            'practitioner_id' => $this->practitionerId,
            'slot_minutes' => 30,
            'generation_days' => 7,
            'timezone' => 'UTC',
        ], $attributes));
    }

    private function book(string $start, int $minutes = 30): Appointment
    {
        $startAt = Carbon::parse($start, 'UTC');

        return app(AppointmentSchedulingService::class)->schedule([
            'branch_id' => $this->branch->id,
            'patient_id' => $this->patient->id,
            'practitioner_primary_id' => $this->practitionerId,
            'status' => AppointmentStatus::BOOKED,
            'start_at' => $startAt,
            'end_at' => $startAt->copy()->addMinutes($minutes),
        ]);
    }

    private function slotAt(AppointmentSchedule $schedule, string $start): AppointmentSlot
    {
        return AppointmentSlot::query()
            ->where('schedule_id', $schedule->id)
            ->where('start_at', Carbon::parse($start, 'UTC'))
            ->sole();
    }

    public function test_generates_weekday_slots_over_the_rolling_window(): void
    {
        $schedule = $this->schedule();

        $result = app(SlotGenerationService::class)->generate($schedule);

        // 7-day window from Monday: Mon–Fri, 4 × 30 min slots per day.
        $this->assertSame(20, $result['created']);
        $this->assertSame(20, $schedule->slots()->where('status', SlotStatus::FREE->value)->count());
    }

    public function test_generation_is_idempotent_and_drops_free_slots_no_longer_offered(): void
    {
        $schedule = $this->schedule();
        $generator = app(SlotGenerationService::class);
        $generator->generate($schedule);

        $this->assertSame(['created' => 0, 'removed' => 0], $generator->generate($schedule->refresh()));

        $schedule->availabilities()->update(['end_time' => '10:00']);
        $result = $generator->generate($schedule->refresh());

        $this->assertSame(10, $result['removed']);
        $this->assertSame(10, $schedule->slots()->count());
    }

    public function test_slots_respect_the_planning_horizon_and_schedule_timezone(): void
    {
        $schedule = $this->schedule([
            'timezone' => 'Africa/Lagos', // UTC+1
            'planning_horizon_end' => now()->toDateString(), // Monday only
        ]);

        app(SlotGenerationService::class)->generate($schedule);

        $first = $schedule->slots()->orderBy('start_at')->first();
        $this->assertSame(4, $schedule->slots()->count());
        $this->assertSame('08:00', $first->start_at->copy()->setTimezone('UTC')->format('H:i'));
    }

    public function test_booking_marks_the_slot_busy_and_cancelling_frees_it(): void
    {
        $schedule = $this->schedule();
        app(SlotGenerationService::class)->generate($schedule);
        $nineAm = now()->setTime(9, 0)->toDateTimeString();

        $appointment = $this->book($nineAm);
        $slot = $this->slotAt($schedule, $nineAm);
        $this->assertSame(SlotStatus::BUSY, $slot->status);
        $this->assertSame($appointment->id, $slot->appointment_id);

        app(AppointmentSchedulingService::class)->cancel($appointment);
        $this->assertSame(SlotStatus::FREE, $slot->refresh()->status);
        $this->assertNull($slot->appointment_id);
    }

    public function test_rescheduling_moves_the_busy_slot(): void
    {
        $schedule = $this->schedule();
        app(SlotGenerationService::class)->generate($schedule);
        $appointment = $this->book(now()->setTime(9, 0)->toDateTimeString());

        $newStart = now()->setTime(10, 0);
        app(AppointmentSchedulingService::class)->reschedule($appointment, [
            'start_at' => $newStart,
            'end_at' => $newStart->copy()->addMinutes(30),
        ]);

        $this->assertSame(SlotStatus::FREE, $this->slotAt($schedule, now()->setTime(9, 0)->toDateTimeString())->status);
        $this->assertSame(SlotStatus::BUSY, $this->slotAt($schedule, $newStart->toDateTimeString())->status);
    }

    public function test_schedule_block_marks_slots_blocked_and_bookings_are_rejected(): void
    {
        $schedule = $this->schedule();
        app(SlotGenerationService::class)->generate($schedule);

        ScheduleBlock::create([
            'branch_id' => $this->branch->id,
            'practitioner_id' => $this->practitionerId,
            'blocked_from' => now()->setTime(9, 0),
            'blocked_to' => now()->setTime(10, 0),
        ]);

        $this->assertSame(SlotStatus::BLOCKED, $this->slotAt($schedule, now()->setTime(9, 30)->toDateTimeString())->status);
        $this->assertSame(SlotStatus::FREE, $this->slotAt($schedule, now()->setTime(10, 0)->toDateTimeString())->status);

        $this->expectException(AppointmentConflictException::class);
        $this->book(now()->setTime(9, 30)->toDateTimeString());
    }

    public function test_booking_outside_scheduled_hours_is_rejected(): void
    {
        $schedule = $this->schedule();
        app(SlotGenerationService::class)->generate($schedule);

        $this->expectException(OutsideScheduleException::class);
        $this->book(now()->setTime(14, 0)->toDateTimeString());
    }

    public function test_booking_spanning_past_closing_time_is_rejected(): void
    {
        $schedule = $this->schedule();
        app(SlotGenerationService::class)->generate($schedule);

        $this->expectException(OutsideScheduleException::class);
        $this->book(now()->setTime(10, 30)->toDateTimeString(), 60);
    }

    public function test_multi_slot_booking_inside_hours_is_accepted(): void
    {
        $schedule = $this->schedule();
        app(SlotGenerationService::class)->generate($schedule);

        $this->book(now()->setTime(9, 0)->toDateTimeString(), 60);

        $this->assertSame(2, $schedule->slots()->where('status', SlotStatus::BUSY->value)->count());
    }

    public function test_booking_beyond_the_rolling_window_generates_that_day_on_demand(): void
    {
        $schedule = $this->schedule(['generation_days' => 1]);
        app(SlotGenerationService::class)->generate($schedule);
        $later = now()->addWeeks(3)->setTime(9, 0);

        $this->book($later->toDateTimeString());

        $this->assertSame(SlotStatus::BUSY, $this->slotAt($schedule, $later->toDateTimeString())->status);
    }

    public function test_practitioners_without_a_schedule_or_with_enforcement_off_are_unrestricted(): void
    {
        $this->book(now()->setTime(22, 0)->toDateTimeString());

        $this->schedule();
        AppointmentSettings::fake(['enforce_schedules' => false, 'external_sync_enabled' => false]);
        $this->book(now()->addDay()->setTime(22, 0)->toDateTimeString());

        $this->assertSame(2, Appointment::query()->where('practitioner_primary_id', $this->practitionerId)->count());
    }

    public function test_generate_slots_command_covers_active_schedules_only(): void
    {
        $active = $this->schedule();
        $inactive = $this->schedule(['is_active' => false, 'practitioner_id' => (string) Str::uuid()]);

        $this->artisan('appointments:generate-slots')->assertSuccessful();

        $this->assertSame(20, $active->slots()->count());
        $this->assertSame(0, $inactive->slots()->count());
    }
}
