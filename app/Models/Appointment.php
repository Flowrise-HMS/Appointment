<?php

namespace Modules\Appointment\Models;

use App\Models\User;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Appointment\Database\Factories\AppointmentFactory;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\AppointmentType;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\AppointmentResource as AppointmentFilamentResource;
use Modules\Core\Models\BaseModel;
use Modules\Core\Models\Department;
use Modules\Core\Models\Location;
use Modules\Patient\Models\Patient;
use Modules\Staff\Models\Staff;

class Appointment extends BaseModel implements Eventable
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'patient_id',
        'practitioner_primary_id',
        'location_id',
        'department_id',
        'status',
        'appointment_type',
        'priority',
        'service_category_code',
        'service_type_code',
        'reason_code',
        'reason_text',
        'start_at',
        'end_at',
        'checked_in_at',
        'completed_at',
        'cancellation_reason_code',
        'external_reference',
        'idempotency_key',
        'notes_encrypted',
        'created_by',
        'updated_by',
        'version',
    ];

    protected $casts = [
        'status' => AppointmentStatus::class,
        'appointment_type' => AppointmentType::class,
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'completed_at' => 'datetime',
        'priority' => 'integer',
        'version' => 'integer',
        'notes_encrypted' => 'encrypted',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(AppointmentParticipant::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(AppointmentResource::class);
    }

    public function recurrenceRules(): HasMany
    {
        return $this->hasMany(AppointmentRecurrenceRule::class);
    }

    public function appointmentAudits(): HasMany
    {
        return $this->hasMany(AppointmentAudit::class);
    }

    /**
     * Integration outbox rows for this appointment ({@see AppointmentSyncOutbox::AGGREGATE_TYPE_APPOINTMENT}).
     */
    public function syncOutboxEntries(): HasMany
    {
        return $this->hasMany(AppointmentSyncOutbox::class, 'aggregate_id')
            ->where('aggregate_type', AppointmentSyncOutbox::AGGREGATE_TYPE_APPOINTMENT);
    }

    /**
     * Primary clinician reference (UUID); no DB FK — optional link to Staff when IDs align.
     */
    public function primaryPractitioner(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'practitioner_primary_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function newFactory(): AppointmentFactory
    {
        return AppointmentFactory::new();
    }

    public function toCalendarEvent(): CalendarEvent
    {
        $patient = $this->patient;
        $mrn = $patient?->mrn;
        $name = $patient
            ? trim(implode(' ', array_filter([$patient->first_name, $patient->last_name])))
            : '';

        $reason = $this->reason_text ? Str::limit((string) $this->reason_text, 48) : null;
        $titleParts = array_filter([$mrn ? "MRN {$mrn}" : null, $name ?: null, $reason]);
        $title = $titleParts !== []
            ? implode(' · ', $titleParts)
            : __('Appointment');

        $colors = $this->status->calendarEventColors();

        $event = CalendarEvent::make($this)
            ->title($title)
            ->start($this->start_at)
            ->end($this->end_at)
            ->backgroundColor($colors['background'])
            ->textColor($colors['text']);

        $url = AppointmentFilamentResource::getUrl('view', ['record' => $this]);
        if (filled($url)) {
            $event->url($url, '_self');
        }

        return $event;
    }
}
