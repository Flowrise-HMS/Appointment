<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Appointment\Enums\SyncOutboxStatus;
use Modules\Core\Models\BaseModel;

class AppointmentSyncOutbox extends BaseModel
{
    use HasFactory, HasUuids;

    protected $table = 'appointment_sync_outbox';

    protected $fillable = [
        'branch_id',
        'aggregate_type',
        'aggregate_id',
        'event_name',
        'idempotency_key',
        'payload',
        'available_at',
        'processed_at',
        'attempts',
        'status',
        'last_error',
    ];

    protected $casts = [
        'status' => SyncOutboxStatus::class,
        'payload' => 'array',
        'available_at' => 'datetime',
        'processed_at' => 'datetime',
        'attempts' => 'integer',
    ];
}
