<?php

namespace Modules\Appointment\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Appointment\Classes\Services\AppointmentSchedulingService;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\AppointmentType;
use Modules\Appointment\Models\Appointment;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentSchedulingService $schedulingService) {}

    public function index(Request $request): JsonResponse
    {
        $appointments = Appointment::query()
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = AppointmentStatus::tryFrom((string) $request->string('status'));

                return $status ? $query->where('status', $status) : $query;
            })
            ->when($request->filled('patient_id'), fn ($query) => $query->where('patient_id', $request->string('patient_id')))
            ->orderBy('start_at')
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json($appointments);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'patient_id' => ['required', 'uuid'],
            'branch_id' => ['required', 'uuid'],
            'location_id' => ['nullable', 'uuid'],
            'department_id' => ['nullable', 'uuid'],
            'practitioner_primary_id' => ['nullable', 'uuid'],
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'appointment_type' => ['nullable', Rule::enum(AppointmentType::class)],
            'priority' => ['nullable', 'integer', 'between:0,99'],
            'reason_code' => ['nullable', 'string'],
            'reason_text' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'notes_encrypted' => ['nullable', 'string'],
        ]);

        $appointment = $this->schedulingService->schedule($payload);

        return response()->json($appointment, 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        return response()->json($appointment->load(['participants', 'resources', 'recurrenceRules']));
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        $payload = $request->validate([
            'location_id' => ['nullable', 'uuid'],
            'department_id' => ['nullable', 'uuid'],
            'practitioner_primary_id' => ['nullable', 'uuid'],
            'status' => ['nullable', Rule::enum(AppointmentStatus::class)],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after:start_at'],
            'reason_text' => ['nullable', 'string'],
            'notes_encrypted' => ['nullable', 'string'],
        ]);

        $updated = $this->schedulingService->reschedule($appointment, $payload);

        return response()->json($updated);
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $appointment->delete();

        return response()->json([], 204);
    }

    public function checkIn(Appointment $appointment): JsonResponse
    {
        return response()->json($this->schedulingService->checkIn($appointment));
    }

    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        $payload = $request->validate([
            'cancellation_reason_code' => ['nullable', 'string'],
        ]);

        return response()->json(
            $this->schedulingService->cancel($appointment, $payload['cancellation_reason_code'] ?? null)
        );
    }

    public function bulkReschedule(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'appointment_ids' => ['required', 'array'],
            'appointment_ids.*' => ['uuid'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
        ]);

        $updated = Appointment::query()
            ->whereIn('id', $payload['appointment_ids'])
            ->get()
            ->map(fn (Appointment $appointment) => $this->schedulingService->reschedule($appointment, [
                'start_at' => $payload['start_at'],
                'end_at' => $payload['end_at'],
            ]));

        return response()->json(['count' => $updated->count()]);
    }
}
