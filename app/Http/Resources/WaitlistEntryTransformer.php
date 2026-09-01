<?php

namespace Modules\Appointment\Http\Resources;

use Illuminate\Http\Request;
use Modules\Appointment\Models\WaitlistEntry;
use Modules\Core\Http\Resources\ApiTransformer;

/**
 * @property WaitlistEntry $resource
 */
class WaitlistEntryTransformer extends ApiTransformer
{
    public function toArray(Request $request): array
    {
        return $this->filterFields([
            'id' => $this->resource->id,
            'patient_id' => $this->resource->patient_id,
            'preferred_practitioner_id' => $this->resource->preferred_practitioner_id,
            'preferred_location_id' => $this->resource->preferred_location_id,
            'preferred_department_id' => $this->resource->preferred_department_id,
            'urgency_score' => $this->resource->urgency_score,
            'wait_time_score' => $this->resource->wait_time_score,
            'referral_score' => $this->resource->referral_score,
            'manual_override_score' => $this->resource->manual_override_score,
            'computed_priority_score' => $this->resource->computed_priority_score,
            'status' => $this->resource->status?->value,
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * `branch_id` is deliberately absent: the caller is already pinned to a single
     * branch by SetCurrentApiBranch, so echoing it back only discloses tenant
     * topology.
     */
    protected function allowedFields(): array
    {
        return [
            'id',
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
            'created_at',
            'updated_at',
        ];
    }
}
