<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Appointment\Database\Factories\AppointmentScheduleFactory;
use Modules\Core\Models\BaseModel;
use Modules\Core\Models\Department;
use Modules\Core\Models\Location;
use Modules\Core\Models\Service;

/**
 * When a practitioner (or service / location) can be booked. Weekly
 * availability windows are expanded into {@see AppointmentSlot} rows by
 * {@see \Modules\Appointment\Classes\Services\SlotGenerationService}.
 *
 * @property string $id
 * @property string $branch_id
 * @property string $name
 * @property string|null $practitioner_id
 * @property string|null $location_id
 * @property string|null $department_id
 * @property string|null $service_id
 * @property string|null $resource_reference
 * @property int $slot_minutes
 * @property Carbon|null $planning_horizon_start
 * @property Carbon|null $planning_horizon_end
 * @property int $generation_days
 * @property string|null $timezone
 * @property bool $is_active
 * @property string|null $comment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ScheduleAvailability> $availabilities
 * @property-read \Modules\Staff\Models\Staff|null $practitioner
 *
 * @method BelongsTo practitioner()
 */
class AppointmentSchedule extends BaseModel
{
    use HasFactory, HasUuids;

    protected $table = 'appointment_schedules';

    protected $fillable = [
        'branch_id',
        'name',
        'practitioner_id',
        'location_id',
        'department_id',
        'service_id',
        'resource_reference',
        'slot_minutes',
        'planning_horizon_start',
        'planning_horizon_end',
        'generation_days',
        'timezone',
        'is_active',
        'comment',
    ];

    protected $attributes = [
        'is_active' => true,
        'slot_minutes' => 15,
        'generation_days' => 28,
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'slot_minutes' => 'integer',
        'generation_days' => 'integer',
        'planning_horizon_start' => 'date',
        'planning_horizon_end' => 'date',
    ];

    protected static function newFactory(): AppointmentScheduleFactory
    {
        return AppointmentScheduleFactory::new();
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(ScheduleAvailability::class, 'schedule_id')->orderBy('weekday')->orderBy('start_time');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(AppointmentSlot::class, 'schedule_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Timezone used to interpret availability times: the schedule's own, then
     * the branch's, then the application's.
     */
    public function effectiveTimezone(): string
    {
        $candidates = [$this->timezone, $this->branch?->timezone, (string) config('app.timezone', 'UTC')];

        foreach ($candidates as $timezone) {
            if (filled($timezone) && in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
                return (string) $timezone;
            }
        }

        return 'UTC';
    }

    /**
     * Whether the planning horizon covers the given instant.
     */
    public function coversDate(Carbon $at): bool
    {
        $day = $at->copy()->setTimezone($this->effectiveTimezone())->toDateString();

        if ($this->planning_horizon_start !== null && $day < $this->planning_horizon_start->toDateString()) {
            return false;
        }

        return $this->planning_horizon_end === null || $day <= $this->planning_horizon_end->toDateString();
    }
}
