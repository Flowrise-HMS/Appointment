<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Core\Models\BaseModel;
use Modules\Core\Models\Department;
use Modules\Core\Models\Location;
use Modules\Staff\Models\Staff;

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

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function practitioner()
    {
        return $this->belongsTo(Staff::class, 'practitioner_id');
    }
}
