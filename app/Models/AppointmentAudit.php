<?php

namespace Modules\Appointment\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\BaseModel;

class AppointmentAudit extends BaseModel
{
    use HasFactory, HasUuids;

    protected $table = 'appointment_audits';

    protected $fillable = [
        'appointment_id',
        'branch_id',
        'actor_id',
        'action',
        'before_payload',
        'after_payload',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected $casts = [
        'before_payload' => 'array',
        'after_payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
