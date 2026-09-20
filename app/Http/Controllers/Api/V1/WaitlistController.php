<?php

namespace Modules\Appointment\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Appointment\Classes\Services\WaitlistScoringService;
use Modules\Appointment\Enums\WaitlistEntryStatus;
use Modules\Appointment\Http\Requests\WaitlistEntryRequest;
use Modules\Appointment\Http\Resources\WaitlistEntryTransformer;
use Modules\Appointment\Models\WaitlistEntry;
use Modules\Appointment\Settings\AppointmentSettings;
use Modules\Core\Http\Controllers\Api\ApiController;
use Modules\Core\Http\Responses\ApiResponse;

class WaitlistController extends ApiController
{
    public function __construct(protected WaitlistScoringService $scoringService)
    {
        $settings = app(AppointmentSettings::class);

        abort_unless($settings->waitlist_enabled && $settings->waitlist_api_enabled, 404, 'Waitlist API is disabled.');
    }

    /**
     * @group Waitlist
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeApi('viewAny', WaitlistEntry::class);

        return ApiResponse::paginated(
            WaitlistEntry::query()
                ->when($request->filled('status'), function ($query) use ($request) {
                    $status = enum_try_from(WaitlistEntryStatus::class, $request->string('status')->toString());

                    return $status ? $query->where('status', $status) : $query;
                })
                ->orderByDesc('computed_priority_score'),
            WaitlistEntryTransformer::class,
            (int) $request->integer('per_page', 20),
        );
    }

    /**
     * @group Waitlist
     */
    public function store(WaitlistEntryRequest $request): JsonResponse
    {
        $this->authorizeApi('create', WaitlistEntry::class);

        $entry = $this->scoringService->createEntry($request->validated());

        return ApiResponse::created(new WaitlistEntryTransformer($entry));
    }

    /**
     * @group Waitlist
     */
    public function offerSlot(WaitlistEntry $waitlistEntry): JsonResponse
    {
        $this->authorizeApi('update', $waitlistEntry);

        $waitlistEntry->update(['status' => WaitlistEntryStatus::OFFERED]);

        return ApiResponse::ok(new WaitlistEntryTransformer($waitlistEntry->fresh()));
    }
}
