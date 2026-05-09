<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Models\BaseModel;

class ScheduleBlock extends BaseModel
{
    use HasFactory, HasUuids;

    protected $table = 'appointment_schedule_blocks';

    protected $fillable = [
        'branch_id',
        'practitioner_id',
        'location_id',
        'department_id',
        'resource_reference',
        'reason',
        'blocked_from',
        'blocked_to',
    ];

    protected $casts = [
        'blocked_from' => 'datetime',
        'blocked_to' => 'datetime',
    ];
}
