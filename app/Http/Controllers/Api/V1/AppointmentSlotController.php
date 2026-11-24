<?php

namespace Modules\Appointment\Http\Controllers\Api\V1;

use Illuminate\Database\Eloquent\Builder;
use Modules\Appointment\Models\AppointmentSlot;
use Modules\Core\Http\Controllers\Api\ResourceApiController;

/**
 * @group Appointments
 *
 * Generated bookable slots (read-only). Filter `status=free` and a practitioner
 * or service to find availability; upcoming slots only.
 */
class AppointmentSlotController extends ResourceApiController
{
    protected string $defaultSort = 'start_at';

    public static function model(): string
    {
        return AppointmentSlot::class;
    }

    public static function label(): string
    {
        return 'slot';
    }

    public function fields(): array
    {
        return ['id', 'schedule_id', 'practitioner_id', 'service_id', 'start_at', 'end_at', 'status', 'appointment_id'];
    }

    public function filters(): array
    {
        return ['status' => 'status', 'schedule_id' => 'schedule_id', 'practitioner_id' => 'practitioner_id', 'service_id' => 'service_id'];
    }

    public function sortable(): array
    {
        return ['start_at'];
    }

    protected function scopedQuery(): Builder
    {
        return parent::scopedQuery()->where('start_at', '>=', now());
    }
}
