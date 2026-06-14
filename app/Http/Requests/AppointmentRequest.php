<?php

namespace Modules\Appointment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\AppointmentType;

class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $appointmentId = $this->route('appointment')?->id;

        return [
            'patient_id' => ['required', 'uuid', 'exists:patients,id'],
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'practitioner_primary_id' => ['nullable', 'uuid', 'exists:staff,id'],
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'appointment_type' => ['nullable', Rule::enum(AppointmentType::class)],
            'priority' => ['nullable', 'integer', 'between:0,99'],
            'service_category_code' => ['nullable', 'string', 'max:50'],
            'service_type_code' => ['nullable', 'string', 'max:50'],
            'reason_code' => ['nullable', 'string', 'max:100'],
            'reason_text' => ['nullable', 'string', 'max:1000'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'checked_in_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'cancellation_reason_code' => ['nullable', 'string', 'max:100'],
            'external_reference' => ['nullable', 'string', 'max:255'],
            'idempotency_key' => ['nullable', 'string', 'max:255', Rule::unique('appointments', 'idempotency_key')->ignore($appointmentId)],
            'notes_encrypted' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Patient is required.',
            'patient_id.exists' => 'Selected patient does not exist.',
            'branch_id.required' => 'Branch is required.',
            'start_at.required' => 'Start time is required.',
            'end_at.required' => 'End time is required.',
            'end_at.after' => 'End time must be after start time.',
            'idempotency_key.unique' => 'This idempotency key has already been used.',
        ];
    }
}
