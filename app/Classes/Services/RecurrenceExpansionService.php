<?php

namespace Modules\Appointment\Classes\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Appointment\Classes\Data\RecurrenceExpansionResult;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\RecurrenceFrequency;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentRecurrenceRule;

/**
 * Expands a recurrence rule into concrete appointments after its parent.
 *
 * - The parent appointment is the first occurrence; `occurrence_count` is the
 *   number of additional instances to create.
 * - Dates are computed in the rule's timezone so the local time of day is
 *   kept across DST changes, then stored in the application timezone.
 * - Weekly rules honour `by_day` (MON..SUN); without it they repeat on the
 *   parent's weekday.
 * - Expansion is idempotent: every instance carries the reference
 *   `recur:{ruleId}:{localDate}` and existing instances are skipped.
 */
class RecurrenceExpansionService
{
    private const MAX_INSTANCES = 500;

    /**
     * @return Collection<int, Appointment>
     */
    public function expand(AppointmentRecurrenceRule $rule, array $overrides = []): Collection
    {
        return $this->expandWithReport($rule, $overrides)->created;
    }

    public function expandWithReport(AppointmentRecurrenceRule $rule, array $overrides = []): RecurrenceExpansionResult
    {
        if (! $rule->is_active) {
            return RecurrenceExpansionResult::empty();
        }

        $parent = $rule->appointment;

        if ($parent === null || $parent->start_at === null) {
            return RecurrenceExpansionResult::empty();
        }

        $duration = $parent->end_at !== null
            ? max(30, (int) $parent->start_at->diffInMinutes($parent->end_at))
            : 30;

        $created = collect();
        $existing = [];
        $conflicts = [];

        foreach ($this->occurrenceStarts($rule) as $localDate => $start) {
            $reference = $this->instanceReference($rule, $localDate);

            if (Appointment::query()
                ->withoutGlobalScope('branch')
                ->where('branch_id', $parent->branch_id)
                ->where('external_reference', $reference)
                ->exists()) {
                $existing[] = $localDate;

                continue;
            }

            $end = $start->copy()->addMinutes($duration);

            if ($this->hasConflict($parent, $start, $end)) {
                Log::info('RecurrenceExpansion: slot conflict, skipping instance', [
                    'rule_id' => $rule->id,
                    'date' => $localDate,
                ]);
                $conflicts[] = $localDate;

                continue;
            }

            $created->push(Appointment::create(array_merge([
                'branch_id' => $parent->branch_id,
                'patient_id' => $parent->patient_id,
                'practitioner_primary_id' => $parent->practitioner_primary_id,
                'status' => AppointmentStatus::BOOKED,
                'appointment_type' => $parent->appointment_type,
                'start_at' => $start,
                'end_at' => $end,
                'external_reference' => $reference,
            ], $overrides)));
        }

        return new RecurrenceExpansionResult($created, $existing, $conflicts);
    }

    public function cancelSeries(AppointmentRecurrenceRule $rule, string $reasonCode = 'series_cancelled'): int
    {
        $rule->forceFill(['is_active' => false])->save();

        return Appointment::query()
            ->withoutGlobalScope('branch')
            ->where('branch_id', $rule->branch_id)
            ->where('external_reference', 'like', 'recur:'.$rule->id.':%')
            ->where('start_at', '>', now())
            ->whereIn('status', [AppointmentStatus::BOOKED, AppointmentStatus::ARRIVED])
            ->update([
                'status' => AppointmentStatus::CANCELLED,
                'cancellation_reason_code' => $reasonCode,
            ]);
    }

    /**
     * Occurrence start instants (application timezone) keyed by local date.
     *
     * @return array<string, Carbon>
     */
    public function occurrenceStarts(AppointmentRecurrenceRule $rule): array
    {
        $timezone = $this->timezone($rule);
        $appTimezone = (string) config('app.timezone', 'UTC');
        $first = $rule->appointment->start_at->copy()->setTimezone($timezone);
        $interval = max(1, (int) $rule->interval);
        $limit = $rule->occurrence_count !== null
            ? min(self::MAX_INSTANCES, max(0, (int) $rule->occurrence_count))
            : self::MAX_INSTANCES;
        $until = $rule->until_at;

        $starts = [];

        foreach ($this->localDates($rule, $first, $interval) as $localDate) {
            if (count($starts) >= $limit) {
                break;
            }

            $start = Carbon::create(
                $localDate->year,
                $localDate->month,
                $localDate->day,
                $first->hour,
                $first->minute,
                0,
                $timezone,
            )->setTimezone($appTimezone);

            if ($until !== null && $start->gt($until)) {
                break;
            }

            $starts[$localDate->toDateString()] = $start;
        }

        return $starts;
    }

    /**
     * Local calendar dates strictly after the parent's local date, in order.
     *
     * @return \Generator<int, Carbon>
     */
    private function localDates(AppointmentRecurrenceRule $rule, Carbon $first, int $interval): \Generator
    {
        $frequency = enum_try_from(RecurrenceFrequency::class, (string) $rule->frequency) ?? RecurrenceFrequency::WEEKLY;
        $anchor = $first->copy()->startOfDay();
        $days = $rule->byDayNumbers();

        for ($step = 1, $emitted = 0; $step <= self::MAX_INSTANCES * 7 && $emitted < self::MAX_INSTANCES; $step++) {
            if ($frequency === RecurrenceFrequency::DAILY) {
                $emitted++;

                yield $anchor->copy()->addDays($interval * $step);

                continue;
            }

            if ($frequency === RecurrenceFrequency::MONTHLY) {
                $emitted++;

                yield $anchor->copy()->addMonthsNoOverflow($interval * $step);

                continue;
            }

            if ($days === []) {
                $emitted++;

                yield $anchor->copy()->addWeeks($interval * $step);

                continue;
            }

            // Weekly with by_day: the parent's own week counts as week 0.
            $weekStart = $anchor->copy()->startOfWeek(Carbon::MONDAY)->addWeeks($interval * ($step - 1));

            foreach ($days as $isoDay) {
                $date = $weekStart->copy()->addDays($isoDay - 1);

                if ($date->lte($anchor)) {
                    continue;
                }

                $emitted++;

                yield $date;
            }
        }
    }

    private function timezone(AppointmentRecurrenceRule $rule): string
    {
        $timezone = (string) ($rule->timezone ?: config('app.timezone', 'UTC'));

        return in_array($timezone, \DateTimeZone::listIdentifiers(), true) || $timezone === 'UTC'
            ? $timezone
            : (string) config('app.timezone', 'UTC');
    }

    private function instanceReference(AppointmentRecurrenceRule $rule, string $localDate): string
    {
        return 'recur:'.$rule->id.':'.$localDate;
    }

    private function hasConflict(Appointment $parent, Carbon $start, Carbon $end): bool
    {
        if ($parent->practitioner_primary_id === null) {
            return false;
        }

        return Appointment::query()
            ->withoutGlobalScope('branch')
            ->where('branch_id', $parent->branch_id)
            ->where('practitioner_primary_id', $parent->practitioner_primary_id)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->where('status', '!=', AppointmentStatus::CANCELLED)
            ->whereKeyNot($parent->getKey())
            ->exists();
    }
}
