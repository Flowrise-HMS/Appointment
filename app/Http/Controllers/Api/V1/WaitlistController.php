<?php

namespace Modules\Appointment\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Appointment\Classes\Services\WaitlistScoringService;
use Modules\Appointment\Http\Requests\WaitlistEntryRequest;
use Modules\Appointment\Models\WaitlistEntry;
use Modules\Core\Http\Controllers\Api\ApiController;
use Modules\Core\Http\Responses\ApiResponse;

class WaitlistController extends ApiController
{
    public function __construct(protected WaitlistScoringService $scoringService) {}

    /**
     * @group Waitlist
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeApi('viewAny', WaitlistEntry::class);

        $paginator = WaitlistEntry::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByDesc('computed_priority_score')
            ->paginate((int) $request->integer('per_page', 20));

        return ApiResponse::ok(
            $paginator->items(),
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        );
    }

    /**
     * @group Waitlist
     */
    public function store(WaitlistEntryRequest $request): JsonResponse
    {
        $this->authorizeApi('create', WaitlistEntry::class);

        $payload = $request->validated();

        $payload['computed_priority_score'] = $this->scoringService->score(
            $payload['urgency_score'],
            $payload['wait_time_score'],
            $payload['referral_score'],
            $payload['manual_override_score'] ?? 0
        );

        $entry = WaitlistEntry::create($payload);

        return ApiResponse::ok($entry, 201);
    }

    /**
     * @group Waitlist
     */
    public function offerSlot(WaitlistEntry $waitlistEntry): JsonResponse
    {
        $this->authorizeApi('update', $waitlistEntry);

        $waitlistEntry->update(['status' => 'offered']);

        return ApiResponse::ok($waitlistEntry->fresh());
    }
}
