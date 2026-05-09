<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\BaseModel;

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
        return $this->belongsTo(Appointment::class);
    }
}
