<?php

namespace Modules\Appointment\Http\Resources;

use Illuminate\Http\Request;
use Modules\Appointment\Models\Appointment;
use Modules\Core\Http\Resources\ApiTransformer;

/** @property Appointment $resource */
class AppointmentTransformer extends ApiTransformer
{
    public function toArray(Request $request): array
    {
        return $this->filterFields([
            'id' => $this->resource->id,
            'branch_id' => $this->resource->branch_id,
            'patient_id' => $this->resource->patient_id,
            'practitioner_primary_id' => $this->resource->practitioner_primary_id,
            'location_id' => $this->resource->location_id,
            'department_id' => $this->resource->department_id,
            'status' => $this->resource->status?->value,
            'appointment_type' => $this->resource->appointment_type?->value,
            'priority' => $this->resource->priority,
            'service_id' => $this->resource->service_id,
            'service_category_code' => $this->resource->service_category_code,
            'service_type_code' => $this->resource->service_type_code,
            'coverage_type' => $this->resource->coverage_type?->value,
            'reason_code' => $this->resource->reason_code,
            'reason_text' => $this->resource->reason_text,
            'start_at' => $this->resource->start_at?->toIso8601String(),
            'end_at' => $this->resource->end_at?->toIso8601String(),
            'checked_in_at' => $this->resource->checked_in_at?->toIso8601String(),
            'completed_at' => $this->resource->completed_at?->toIso8601String(),
            'cancellation_reason_code' => $this->resource->cancellation_reason_code,
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
        ]);
    }

    protected function allowedFields(): array
    {
        return [
            'id',
            'branch_id',
            'patient_id',
            'practitioner_primary_id',
            'status',
            'appointment_type',
            'priority',
            'start_at',
            'end_at',
            'checked_in_at',
            'completed_at',
            'cancellation_reason_code',
            'reason_text',
            'created_at',
            'updated_at',
        ];
    }
}
