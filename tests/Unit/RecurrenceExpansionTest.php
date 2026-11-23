<?php

namespace Modules\Appointment\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
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

    /**
     * @param  array<string, mixed>  $ruleAttributes
     */
    private function ruleStartingAt(Carbon $start, array $ruleAttributes): AppointmentRecurrenceRule
    {
        $parent = Appointment::create([
            'branch_id' => $this->branch->id,
            'patient_id' => $this->patient->id,
            'status' => AppointmentStatus::BOOKED,
            'start_at' => $start,
            'end_at' => $start->copy()->addMinutes(30),
            'external_reference' => 'recur-test-'.uniqid(),
        ]);

        return AppointmentRecurrenceRule::create(array_merge([
            'appointment_id' => $parent->id,
            'frequency' => 'weekly',
            'interval' => 1,
            'timezone' => 'Africa/Accra',
        ], $ruleAttributes));
    }

    public function test_weekly_rule_honours_by_day(): void
    {
        // Monday 09:00 parent; MON/WED/FRI => Wed, Fri, Mon, Wed.
        $monday = Carbon::parse('next monday 09:00', 'UTC')->addWeek();
        $rule = $this->ruleStartingAt($monday, ['by_day' => ['MON', 'WED', 'FRI'], 'occurrence_count' => 4]);

        $dates = app(RecurrenceExpansionService::class)->expand($rule)
            ->map(fn (Appointment $a): string => $a->start_at->toDateString())
            ->all();

        $this->assertSame([
            $monday->copy()->addDays(2)->toDateString(),
            $monday->copy()->addDays(4)->toDateString(),
            $monday->copy()->addDays(7)->toDateString(),
            $monday->copy()->addDays(9)->toDateString(),
        ], $dates);
    }

    public function test_by_day_accepts_a_comma_separated_string_and_every_other_week(): void
    {
        $monday = Carbon::parse('next monday 09:00', 'UTC')->addWeek();
        $rule = $this->ruleStartingAt($monday, ['by_day' => 'TUE,THU', 'interval' => 2, 'occurrence_count' => 4]);

        $dates = array_keys(app(RecurrenceExpansionService::class)->occurrenceStarts($rule));

        $this->assertSame([
            $monday->copy()->addDays(1)->toDateString(),
            $monday->copy()->addDays(3)->toDateString(),
            $monday->copy()->addDays(15)->toDateString(),
            $monday->copy()->addDays(17)->toDateString(),
        ], $dates);
    }

    public function test_until_date_stops_expansion(): void
    {
        $start = now()->addWeek()->startOfDay()->setTime(10, 0);
        $rule = $this->ruleStartingAt($start, [
            'frequency' => 'daily',
            'until_at' => $start->copy()->addDays(3)->setTime(23, 0),
        ]);

        $this->assertCount(3, app(RecurrenceExpansionService::class)->expand($rule));
    }

    public function test_occurrence_count_wins_when_reached_before_until(): void
    {
        $start = now()->addWeek()->startOfDay()->setTime(10, 0);
        $rule = $this->ruleStartingAt($start, [
            'frequency' => 'daily',
            'occurrence_count' => 2,
            'until_at' => $start->copy()->addDays(10),
        ]);

        $this->assertCount(2, app(RecurrenceExpansionService::class)->expand($rule));
    }

    public function test_monthly_rule_does_not_overflow_short_months(): void
    {
        $start = Carbon::create(now()->year + 1, 1, 31, 9, 0, 0, 'UTC');
        $rule = $this->ruleStartingAt($start, ['frequency' => 'monthly', 'occurrence_count' => 2]);

        $dates = array_keys(app(RecurrenceExpansionService::class)->occurrenceStarts($rule));

        $this->assertSame(sprintf('%d-02-%02d', $start->year, Carbon::create($start->year, 2, 1)->daysInMonth), $dates[0]);
        $this->assertSame(sprintf('%d-03-31', $start->year), $dates[1]);
    }

    public function test_local_time_is_kept_across_a_dst_change(): void
    {
        // 09:00 New York on the Monday before US DST starts (EST, UTC-5);
        // the next weekly occurrence is in EDT (UTC-4) and must still be 09:00 local.
        $year = now()->year + 1;
        $dstStart = Carbon::parse("second sunday of march {$year}", 'America/New_York');
        $local = $dstStart->copy()->subDays(6)->setTime(9, 0);
        $rule = $this->ruleStartingAt($local->copy()->setTimezone('UTC'), [
            'timezone' => 'America/New_York',
            'occurrence_count' => 1,
        ]);

        $next = array_values(app(RecurrenceExpansionService::class)->occurrenceStarts($rule))[0];

        $this->assertSame('09:00', $next->copy()->setTimezone('America/New_York')->format('H:i'));
        $this->assertSame('13:00', $next->copy()->setTimezone('UTC')->format('H:i'));
    }

    public function test_report_lists_existing_and_conflicting_dates(): void
    {
        $practitionerId = (string) Str::uuid();
        $rule = $this->rule(interval: 1, occurrenceCount: 3, practitionerId: $practitionerId);
        $service = app(RecurrenceExpansionService::class);

        $first = $service->expandWithReport($rule);
        $this->assertCount(3, $first->created);

        $rule->forceFill(['occurrence_count' => 4])->save();
        $conflictStart = $rule->appointment->start_at->copy()->addWeeks(4);
        Appointment::create([
            'branch_id' => $this->branch->id,
            'patient_id' => $this->patient->id,
            'practitioner_primary_id' => $practitionerId,
            'status' => AppointmentStatus::BOOKED,
            'start_at' => $conflictStart,
            'end_at' => $conflictStart->copy()->addMinutes(30),
            'external_reference' => 'conflict-'.uniqid(),
        ]);

        $second = $service->expandWithReport($rule->refresh());

        $this->assertCount(0, $second->created);
        $this->assertCount(3, $second->existing);
        $this->assertSame([$conflictStart->toDateString()], $second->conflicts);
        $this->assertStringContainsString('skipped for conflicts', $second->summary());
    }

    public function test_new_rule_inherits_branch_and_branch_timezone(): void
    {
        $this->branch->forceFill(['timezone' => 'Africa/Lagos'])->save();
        $start = now()->addWeek()->startOfDay()->setTime(9, 0);
        $parent = Appointment::create([
            'branch_id' => $this->branch->id,
            'patient_id' => $this->patient->id,
            'status' => AppointmentStatus::BOOKED,
            'start_at' => $start,
            'end_at' => $start->copy()->addMinutes(30),
            'external_reference' => 'recur-test-'.uniqid(),
        ]);

        $rule = AppointmentRecurrenceRule::create([
            'appointment_id' => $parent->id,
            'frequency' => 'weekly',
            'interval' => 1,
            'occurrence_count' => 1,
        ]);

        $this->assertSame($this->branch->id, $rule->branch_id);
        $this->assertSame('Africa/Lagos', $rule->timezone);
    }
}
