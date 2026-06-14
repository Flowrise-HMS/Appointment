<?php

namespace Modules\Appointment\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Appointment\Classes\Services\AppointmentSchedulingService;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Http\Requests\AppointmentRequest;
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

    public function store(AppointmentRequest $request): JsonResponse
    {
        $appointment = $this->schedulingService->schedule($request->validated());

        return response()->json($appointment, 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        return response()->json($appointment->load(['participants', 'resources', 'recurrenceRules']));
    }

    public function update(AppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $updated = $this->schedulingService->reschedule($appointment, $request->validated());

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

    public function cancel(AppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        return response()->json(
            $this->schedulingService->cancel($appointment, $request->validated('cancellation_reason_code'))
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
