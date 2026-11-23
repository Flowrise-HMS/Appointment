<?php

namespace Modules\Appointment\Classes\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Appointment\Enums\SlotStatus;
use Modules\Appointment\Models\AppointmentSchedule;
use Modules\Appointment\Models\AppointmentSlot;

/**
 * Materialises bookable slots from a schedule's weekly availability.
 *
 * Generation covers a rolling window (`generation_days` from today, clipped to
 * the planning horizon). It is idempotent: existing slots are kept, missing
 * ones are created, and free future slots that no longer match the
 * availability (e.g. after hours were changed) are removed. Booked or blocked
 * slots are never deleted. Statuses are then recomputed.
 */
class SlotGenerationService
{
    private const MAX_DAYS = 366;

    public function __construct(protected SlotStatusService $statuses) {}

    /**
     * @return array{created: int, removed: int}
     */
    public function generate(AppointmentSchedule $schedule, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $schedule->loadMissing(['availabilities', 'branch']);
        $timezone = $schedule->effectiveTimezone();

        [$firstDay, $lastDay] = $this->window($schedule, $timezone, $from, $to);

        if ($firstDay === null || $lastDay === null || ! $schedule->is_active) {
            return ['created' => 0, 'removed' => 0];
        }

        $expected = $this->expectedSlots($schedule, $timezone, $firstDay, $lastDay);
        $windowStart = $firstDay->copy()->startOfDay()->setTimezone((string) config('app.timezone', 'UTC'));
        $windowEnd = $lastDay->copy()->endOfDay()->setTimezone((string) config('app.timezone', 'UTC'));

        return DB::transaction(function () use ($schedule, $expected, $windowStart, $windowEnd): array {
            $existing = AppointmentSlot::query()
                ->withoutGlobalScope('branch')
                ->where('schedule_id', $schedule->id)
                ->whereBetween('start_at', [$windowStart, $windowEnd])
                ->get()
                ->keyBy(fn (AppointmentSlot $slot): string => $slot->start_at->toIso8601String());

            $created = collect();

            foreach ($expected as $key => [$start, $end]) {
                $slot = $existing->get($key);

                if ($slot === null) {
                    $created->push(AppointmentSlot::create([
                        'schedule_id' => $schedule->id,
                        'branch_id' => $schedule->branch_id,
                        'practitioner_id' => $schedule->practitioner_id,
                        'service_id' => $schedule->service_id,
                        'start_at' => $start,
                        'end_at' => $end,
                        'status' => SlotStatus::FREE,
                    ]));

                    continue;
                }

                if (! $slot->end_at->equalTo($end) || $slot->practitioner_id !== $schedule->practitioner_id || $slot->service_id !== $schedule->service_id) {
                    $slot->forceFill([
                        'end_at' => $end,
                        'practitioner_id' => $schedule->practitioner_id,
                        'service_id' => $schedule->service_id,
                    ])->save();
                }
            }

            $removed = AppointmentSlot::query()
                ->withoutGlobalScope('branch')
                ->where('schedule_id', $schedule->id)
                ->whereBetween('start_at', [$windowStart, $windowEnd])
                ->where('start_at', '>', now())
                ->where('status', SlotStatus::FREE->value)
                ->whereNull('appointment_id')
                ->whereNotIn('start_at', collect($expected)->map(fn (array $pair) => $pair[0])->values()->all())
                ->delete();

            $this->statuses->refresh(
                AppointmentSlot::query()
                    ->withoutGlobalScope('branch')
                    ->where('schedule_id', $schedule->id)
                    ->whereBetween('start_at', [$windowStart, $windowEnd])
                    ->get()
            );

            return ['created' => $created->count(), 'removed' => (int) $removed];
        });
    }

    /**
     * Make sure slots exist for the local day containing $at (used when a
     * booking falls beyond the rolling window).
     */
    public function ensureDay(AppointmentSchedule $schedule, Carbon $at): void
    {
        $local = $at->copy()->setTimezone($schedule->effectiveTimezone());

        $this->generate($schedule, $local->copy()->startOfDay(), $local->copy()->startOfDay());
    }

    /**
     * @return array{0: Carbon|null, 1: Carbon|null} local-day bounds (inclusive)
     */
    private function window(AppointmentSchedule $schedule, string $timezone, ?Carbon $from, ?Carbon $to): array
    {
        $today = now($timezone)->startOfDay();
        $first = ($from?->copy()->setTimezone($timezone) ?? $today)->startOfDay();
        $last = ($to?->copy()->setTimezone($timezone) ?? $today->copy()->addDays(max(1, $schedule->generation_days) - 1))->startOfDay();

        if ($schedule->planning_horizon_start !== null) {
            $start = Carbon::parse($schedule->planning_horizon_start->toDateString(), $timezone);
            $first = $first->max($start);
        }

        if ($schedule->planning_horizon_end !== null) {
            $end = Carbon::parse($schedule->planning_horizon_end->toDateString(), $timezone);
            $last = $last->min($end);
        }

        if ($first->diffInDays($last, false) > self::MAX_DAYS) {
            $last = $first->copy()->addDays(self::MAX_DAYS);
        }

        return $first->lte($last) ? [$first, $last] : [null, null];
    }

    /**
     * @return array<string, array{0: Carbon, 1: Carbon}> keyed by ISO-8601 start (app timezone)
     */
    private function expectedSlots(AppointmentSchedule $schedule, string $timezone, Carbon $firstDay, Carbon $lastDay): array
    {
        $appTimezone = (string) config('app.timezone', 'UTC');
        $minutes = max(5, $schedule->slot_minutes);
        $byWeekday = $schedule->availabilities->groupBy('weekday');
        $slots = [];

        for ($day = $firstDay->copy(); $day->lte($lastDay); $day->addDay()) {
            foreach ($byWeekday->get($day->dayOfWeekIso, collect()) as $window) {
                [$openHour, $openMinute] = array_map('intval', explode(':', (string) $window->start_time));
                [$closeHour, $closeMinute] = array_map('intval', explode(':', (string) $window->end_time));

                $cursor = Carbon::create($day->year, $day->month, $day->day, $openHour, $openMinute, 0, $timezone);
                $close = Carbon::create($day->year, $day->month, $day->day, $closeHour, $closeMinute, 0, $timezone);

                while ($cursor->copy()->addMinutes($minutes)->lte($close)) {
                    $start = $cursor->copy()->setTimezone($appTimezone);
                    $slots[$start->toIso8601String()] = [$start, $start->copy()->addMinutes($minutes)];
                    $cursor->addMinutes($minutes);
                }
            }
        }

        ksort($slots);

        return $slots;
    }
}
