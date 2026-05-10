<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\BaseModel;

class AppointmentResource extends BaseModel
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'appointment_id',
        'branch_id',
        'resource_type',
        'resource_reference',
        'allocated_from',
        'allocated_to',
    ];

    protected $casts = [
        'allocated_from' => 'datetime',
        'allocated_to' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
}
