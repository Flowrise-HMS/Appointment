<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Appointment\Enums\WaitlistEntryStatus;
use Modules\Core\Models\BaseModel;
use Modules\Patient\Models\Patient;

class WaitlistEntry extends BaseModel
{
    use HasFactory, HasUuids;

    protected $table = 'appointment_waitlist_entries';

    protected $fillable = [
        'branch_id',
        'patient_id',
        'preferred_practitioner_id',
        'preferred_location_id',
        'preferred_department_id',
        'urgency_score',
        'wait_time_score',
        'referral_score',
        'manual_override_score',
        'computed_priority_score',
        'status',
    ];

    protected $casts = [
        'status' => WaitlistEntryStatus::class,
        'urgency_score' => 'integer',
        'wait_time_score' => 'integer',
        'referral_score' => 'integer',
        'manual_override_score' => 'integer',
        'computed_priority_score' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
