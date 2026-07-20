<?php

namespace Modules\Appointment\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Appointment\Classes\Services\AppointmentSchedulingService;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Http\Requests\AppointmentRequest;
use Modules\Appointment\Http\Resources\AppointmentTransformer;
use Modules\Appointment\Models\Appointment;
use Modules\Core\Http\Controllers\Api\ApiController;
use Modules\Core\Http\Responses\ApiResponse;

class AppointmentController extends ApiController
{
    public function __construct(protected AppointmentSchedulingService $schedulingService) {}

    /**
     * @group Appointments
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeApi('viewAny', Appointment::class);

        return ApiResponse::paginated(
            Appointment::query()
                ->when($request->filled('status'), function ($query) use ($request) {
                    $status = AppointmentStatus::tryFrom((string) $request->string('status'));

                    return $status ? $query->where('status', $status) : $query;
                })
                ->when($request->filled('patient_id'), fn ($query) => $query->where('patient_id', $request->string('patient_id')))
                ->orderBy('start_at'),
            AppointmentTransformer::class,
            (int) $request->integer('per_page', 20),
        );
    }

    /**
     * @group Appointments
     */
    public function store(AppointmentRequest $request): JsonResponse
    {
        $this->authorizeApi('create', Appointment::class);

        $appointment = $this->schedulingService->schedule($request->validated());

        return ApiResponse::ok($appointment, 201);
    }

    /**
     * @group Appointments
     */
    public function show(Appointment $appointment): JsonResponse
    {
        $this->authorizeApi('view', $appointment);

        $appointment->load(['participants', 'resources', 'recurrenceRules']);

        return ApiResponse::ok($appointment);
    }

    /**
     * @group Appointments
     */
    public function update(AppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeApi('update', $appointment);

        $updated = $this->schedulingService->reschedule($appointment, $request->validated());

        return ApiResponse::ok($updated);
    }

    /**
     * @group Appointments
     */
    public function destroy(Appointment $appointment): JsonResponse
    {
        $this->authorizeApi('delete', $appointment);

        $appointment->delete();

        return ApiResponse::ok(null, 204);
    }

    /**
     * @group Appointments
     */
    public function checkIn(Appointment $appointment): JsonResponse
    {
        $this->authorizeApi('update', $appointment);

        return ApiResponse::ok($this->schedulingService->checkIn($appointment));
    }

    /**
     * @group Appointments
     */
    public function cancel(AppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeApi('update', $appointment);

        return ApiResponse::ok(
            $this->schedulingService->cancel($appointment, $request->validated('cancellation_reason_code'))
        );
    }

    /**
     * @group Appointments
     */
    public function bulkReschedule(Request $request): JsonResponse
    {
        $this->authorizeApi('update', Appointment::class);

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

        return ApiResponse::ok(['count' => $updated->count()]);
    }
}
