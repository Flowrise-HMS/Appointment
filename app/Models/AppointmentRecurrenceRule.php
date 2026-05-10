<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\BaseModel;

/**
 * Recurrence metadata attached to a parent {@see Appointment}.
 *
 * There is **no** runtime engine that expands this rule into additional appointment instances yet.
 * Treat rows as configuration only until an expansion service ships.
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
    ];

    protected $casts = [
        'by_day' => 'array',
        'until_at' => 'datetime',
        'interval' => 'integer',
        'occurrence_count' => 'integer',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
}
