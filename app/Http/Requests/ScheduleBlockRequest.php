<?php

namespace Modules\Appointment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'uuid', 'exists:branches,id'],
            'practitioner_id' => ['nullable', 'uuid', 'exists:staff,id'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'resource_reference' => ['nullable', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:1000'],
            'blocked_from' => ['required', 'date'],
            'blocked_to' => ['required', 'date', 'after:blocked_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Branch is required.',
            'reason.required' => 'Reason is required for blocking a schedule.',
            'blocked_from.required' => 'Block start time is required.',
            'blocked_to.required' => 'Block end time is required.',
            'blocked_to.after' => 'Block end time must be after start time.',
        ];
    }
}
