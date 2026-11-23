<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Appointment\Enums\RecurrenceFrequency;
use Modules\Core\Models\BaseModel;

/**
 * Recurrence rule attached to a parent {@see Appointment}.
 *
 * Expanded into concrete appointments by
 * {@see \Modules\Appointment\Classes\Services\RecurrenceExpansionService}; the
 * Filament create/edit pages and relation manager expand on save.
 *
 * @property string $id
 * @property string $appointment_id
 * @property string $branch_id
 * @property string $frequency
 * @property int $interval
 * @property list<string>|string|null $by_day
 * @property int|null $occurrence_count
 * @property \Illuminate\Support\Carbon|null $until_at
 * @property string $timezone
 * @property bool $is_active
 * @property-read Appointment|null $appointment
 */
class AppointmentRecurrenceRule extends BaseModel
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'appointment_id',
        'branch_id',
        'frequency',
        'interval',
        'by_day',
        'occurrence_count',
        'until_at',
        'timezone',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'by_day' => 'array',
        'until_at' => 'datetime',
        'interval' => 'integer',
        'occurrence_count' => 'integer',
    ];

    protected static function booted(): void
    {
        // Rules inherit the parent appointment's branch and the branch's local
        // timezone, so they are expanded where the appointment actually happens.
        static::creating(function (self $rule): void {
            $parent = $rule->appointment_id !== null
                ? Appointment::query()->withoutGlobalScope('branch')->with('branch')->find($rule->appointment_id)
                : null;

            if ($parent !== null) {
                $rule->branch_id = $parent->branch_id;
            }

            if (blank($rule->timezone)) {
                $rule->timezone = $parent?->branch?->timezone ?: (string) config('app.timezone', 'UTC');
            }
        });
    }

    /**
     * ISO-8601 weekday numbers from `by_day`, accepting ['MON', 'WED'] or "MON,WED".
     *
     * @return list<int>
     */
    public function byDayNumbers(): array
    {
        $raw = $this->by_day;

        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }

        $codes = RecurrenceFrequency::weekdayCodes();
        $days = [];

        foreach ((array) $raw as $code) {
            $code = strtoupper(trim((string) $code));

            if (isset($codes[$code])) {
                $days[] = $codes[$code];
            }
        }

        $days = array_values(array_unique($days));
        sort($days);

        return $days;
    }

    /**
     * Appointments this rule has booked (identified by their `recur:{id}:` reference).
     *
     * @return Builder<Appointment>
     */
    public function generatedAppointments(): Builder
    {
        return Appointment::query()
            ->withoutGlobalScope('branch')
            ->where('branch_id', $this->branch_id)
            ->where('external_reference', 'like', 'recur:'.$this->getKey().':%');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
}
