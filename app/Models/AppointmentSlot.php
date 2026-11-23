<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Appointment\Database\Factories\AppointmentSlotFactory;
use Modules\Appointment\Enums\SlotStatus;
use Modules\Core\Models\BaseModel;

/**
 * A bookable time slot generated from an {@see AppointmentSchedule}.
 *
 * @property string $id
 * @property string $schedule_id
 * @property string $branch_id
 * @property string|null $practitioner_id
 * @property string|null $service_id
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property SlotStatus $status
 * @property string|null $appointment_id
 * @property-read AppointmentSchedule|null $schedule
 * @property-read Appointment|null $appointment
 */
class AppointmentSlot extends BaseModel
{
    use HasFactory, HasUuids;

    protected $table = 'appointment_slots';

    protected $fillable = [
        'schedule_id',
        'branch_id',
        'practitioner_id',
        'service_id',
        'start_at',
        'end_at',
        'status',
        'appointment_id',
    ];

    protected $attributes = [
        'status' => 'free',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'status' => SlotStatus::class,
    ];

    protected static function newFactory(): AppointmentSlotFactory
    {
        return AppointmentSlotFactory::new();
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(AppointmentSchedule::class, 'schedule_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * @param  Builder<AppointmentSlot>  $query
     * @return Builder<AppointmentSlot>
     */
    public function scopeFree(Builder $query): Builder
    {
        return $query->where('status', SlotStatus::FREE->value);
    }

    /**
     * @param  Builder<AppointmentSlot>  $query
     * @return Builder<AppointmentSlot>
     */
    public function scopeOverlapping(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->where('start_at', '<', $end)->where('end_at', '>', $start);
    }
}
