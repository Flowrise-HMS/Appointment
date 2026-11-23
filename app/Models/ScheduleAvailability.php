<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Appointment\Database\Factories\ScheduleAvailabilityFactory;

/**
 * A weekly availability window on a schedule (ISO weekday, local start/end time).
 *
 * @property string $id
 * @property string $schedule_id
 * @property int $weekday
 * @property string $start_time
 * @property string $end_time
 */
class ScheduleAvailability extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'appointment_schedule_availabilities';

    protected $fillable = [
        'schedule_id',
        'weekday',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'weekday' => 'integer',
    ];

    protected static function newFactory(): ScheduleAvailabilityFactory
    {
        return ScheduleAvailabilityFactory::new();
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(AppointmentSchedule::class, 'schedule_id');
    }
}
