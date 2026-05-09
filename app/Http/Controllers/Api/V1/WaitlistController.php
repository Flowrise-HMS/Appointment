<?php

namespace Modules\Appointment\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Appointment\Classes\Services\WaitlistScoringService;
use Modules\Appointment\Models\WaitlistEntry;

class WaitlistController extends Controller
{
    public function __construct(protected WaitlistScoringService $scoringService) {}

    public function index(Request $request): JsonResponse
    {
        $entries = WaitlistEntry::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByDesc('computed_priority_score')
            ->paginate((int) $request->integer('per_page', 20));

        return response()->json($entries);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'branch_id' => ['required', 'uuid'],
            'patient_id' => ['required', 'uuid'],
            'preferred_practitioner_id' => ['nullable', 'uuid'],
            'preferred_location_id' => ['nullable', 'uuid'],
            'preferred_department_id' => ['nullable', 'uuid'],
            'urgency_score' => ['required', 'integer', 'between:0,10'],
            'wait_time_score' => ['required', 'integer', 'between:0,10'],
            'referral_score' => ['required', 'integer', 'between:0,10'],
            'manual_override_score' => ['nullable', 'integer', 'between:0,50'],
        ]);

        $payload['computed_priority_score'] = $this->scoringService->score(
            $payload['urgency_score'],
            $payload['wait_time_score'],
            $payload['referral_score'],
            $payload['manual_override_score'] ?? 0
        );

        $entry = WaitlistEntry::create($payload);

        return response()->json($entry, 201);
    }

    public function offerSlot(WaitlistEntry $waitlistEntry): JsonResponse
    {
        $waitlistEntry->update(['status' => 'offered']);

        return response()->json($waitlistEntry->fresh());
    }
}
