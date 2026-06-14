<?php

namespace Modules\Appointment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Appointment\Enums\WaitlistEntryStatus;

class WaitlistEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'patient_id' => ['required', 'uuid', 'exists:patients,id'],
            'preferred_practitioner_id' => ['nullable', 'uuid', 'exists:staff,id'],
            'preferred_location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'preferred_department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'urgency_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'wait_time_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'referral_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'manual_override_score' => ['nullable', 'integer', 'min:-100', 'max:100'],
            'status' => ['nullable', Rule::enum(WaitlistEntryStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Branch is required.',
            'patient_id.required' => 'Patient is required.',
            'patient_id.exists' => 'Selected patient does not exist.',
        ];
    }
}
