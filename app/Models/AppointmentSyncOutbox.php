<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Appointment\Enums\SyncOutboxStatus;
use Modules\Core\Models\BaseModel;

/**
 * Outbound integration queue for appointment lifecycle events.
 *
 * **Aggregate contract**: `aggregate_type` is a short string discriminator (default `appointment`).
 * When `aggregate_type` is `appointment`, `aggregate_id` is the UUID of {@see Appointment}.
 * For other aggregate types in the future, use a dedicated relation or {@see Builder::where}
 * — do not assume {@see appointment()} resolves correctly unless type is `appointment`.
 */
class AppointmentSyncOutbox extends BaseModel
{
    use HasFactory, HasUuids;

    public const AGGREGATE_TYPE_APPOINTMENT = 'appointment';

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

    /**
     * Parent appointment when {@see $aggregate_type} is {@see AGGREGATE_TYPE_APPOINTMENT}.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'aggregate_id');
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeForAppointmentAggregate(Builder $query): Builder
    {
        return $query->where('aggregate_type', self::AGGREGATE_TYPE_APPOINTMENT);
    }

    /**
     * Outbox rows ready for dispatch (pending and past availability).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeDue(Builder $query): Builder
    {
        return $query
            ->where('status', SyncOutboxStatus::PENDING)
            ->where(function (Builder $q): void {
                $q->whereNull('available_at')
                    ->orWhere('available_at', '<=', now());
            });
    }
}
