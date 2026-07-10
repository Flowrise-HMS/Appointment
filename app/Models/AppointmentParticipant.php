<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Appointment\Database\Factories\AppointmentParticipantFactory;
use Modules\Appointment\Enums\AppointmentParticipantStatus;
use Modules\Core\Models\BaseModel;

class AppointmentParticipant extends BaseModel
{
    use HasFactory, HasUuids;

    protected static function newFactory(): AppointmentParticipantFactory
    {
        return AppointmentParticipantFactory::new();
    }

    protected $fillable = [
        'appointment_id',
        'branch_id',
        'participant_type',
        'participant_type_code',
        'actor_reference',
        'required',
        'status',
    ];

    protected $casts = [
        'status' => AppointmentParticipantStatus::class,
        'required' => 'boolean',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
