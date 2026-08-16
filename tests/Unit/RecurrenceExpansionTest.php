<?php

namespace Modules\Appointment\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Appointment\Classes\Services\RecurrenceExpansionService;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentRecurrenceRule;
use Modules\Core\Models\Branch;
use Modules\Patient\Models\Patient;
use Tests\TestCase;

class RecurrenceExpansionTest extends TestCase
{
    use DatabaseTransactions;

    private Branch $branch;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateModules(['Core', 'Patient', 'Appointment']);
        $this->branch = Branch::factory()->create();
        $this->patient = Patient::factory()->create(['branch_id' => $this->branch->id]);
    }

    private function rule(int $interval = 1, ?int $occurrenceCount = null, ?string $until = null, ?string $practitionerId = null): AppointmentRecurrenceRule
    {
        $parent = Appointment::create([
            'branch_id' => $this->branch->id,
            'patient_id' => $this->patient->id,
            'practitioner_primary_id' => $practitionerId,
            'status' => AppointmentStatus::BOOKED,
            'start_at' => now()->addWeeks(2)->startOfDay()->setTime(9, 0),
            'end_at' => now()->addWeeks(2)->startOfDay()->setTime(9, 30),
            'external_reference' => 'recur-test-'.uniqid(),
        ]);

        return AppointmentRecurrenceRule::create([
            'appointment_id' => $parent->id,
            'branch_id' => $this->branch->id,
            'frequency' => 'weekly',
            'interval' => $interval,
            'occurrence_count' => $occurrenceCount,
            'until_at' => $until,
            'timezone' => 'Africa/Accra',
        ]);
    }

    public function test_expands_four_weekly_anc_returns(): void
    {
        $rule = $this->rule(interval: 4, occurrenceCount: 4);

        $instances = app(RecurrenceExpansionService::class)->expand($rule);

        $this->assertCount(4, $instances);
        $this->assertSame($this->patient->id, $instances->first()->patient_id);
        $this->assertSame(
            $rule->appointment->start_at->copy()->addWeeks(4)->toDateString(),
            $instances->first()->start_at->toDateString(),
        );
    }

    public function test_expand_is_idempotent(): void
    {
        $rule = $this->rule(interval: 4, occurrenceCount: 3);
        $service = app(RecurrenceExpansionService::class);

        $service->expand($rule);
        $service->expand($rule);

        $this->assertSame(
            3,
            Appointment::where('external_reference', 'like', 'recur:'.$rule->id.':%')->count(),
        );
    }

    public function test_skips_conflicting_slots(): void
    {
        $practitionerId = (string) Str::uuid();
        $rule = $this->rule(interval: 4, occurrenceCount: 2, practitionerId: $practitionerId);
        $firstStart = $rule->appointment->start_at->copy()->addWeeks(4);

        Appointment::create([
            'branch_id' => $this->branch->id,
            'patient_id' => $this->patient->id,
            'practitioner_primary_id' => $practitionerId,
            'status' => AppointmentStatus::BOOKED,
            'start_at' => $firstStart,
            'end_at' => $firstStart->copy()->addMinutes(30),
            'external_reference' => 'conflict-'.uniqid(),
        ]);

        Log::spy();
        $instances = app(RecurrenceExpansionService::class)->expand($rule);
        Log::shouldHaveReceived('info')->once();

        $this->assertCount(1, $instances);
    }

    public function test_inactive_rule_does_not_expand(): void
    {
        $rule = $this->rule(interval: 4, occurrenceCount: 2);
        $rule->forceFill(['is_active' => false])->save();

        $this->assertEmpty(app(RecurrenceExpansionService::class)->expand($rule));
    }

    public function test_cancel_series_cascades_to_future_instances_only(): void
    {
        $rule = $this->rule(interval: 4, occurrenceCount: 3);
        $service = app(RecurrenceExpansionService::class);
        $service->expand($rule);

        $first = Appointment::where('external_reference', 'like', 'recur:'.$rule->id.':%')
            ->orderBy('start_at')
            ->first();
        $first->forceFill(['start_at' => now()->subDay(), 'end_at' => now()->subDay()->addMinutes(30)])->save();

        $service->cancelSeries($rule);

        $rule->refresh();
        $this->assertFalse((bool) $rule->is_active);

        $counts = Appointment::where('external_reference', 'like', 'recur:'.$rule->id.':%')
            ->where('status', AppointmentStatus::CANCELLED)
            ->count();
        $this->assertSame(2, $counts);

        $past = Appointment::where('external_reference', 'like', 'recur:'.$rule->id.':%')
            ->orderBy('start_at')
            ->first();
        $this->assertNotSame(AppointmentStatus::CANCELLED, $past->status);
    }
}
