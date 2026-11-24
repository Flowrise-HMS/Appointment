<?php

namespace Modules\Appointment\Http\Controllers\Api\V1;

use Illuminate\Database\Eloquent\Model;
use Modules\Appointment\Classes\Services\ScheduleBlockService;
use Modules\Appointment\Models\ScheduleBlock;
use Modules\Core\Http\Controllers\Api\ResourceApiController;

/**
 * @group Appointments
 *
 * Unavailability blocks (leave, meetings, maintenance). Overlapping slots become blocked.
 */
class ScheduleBlockController extends ResourceApiController
{
    protected string $defaultSort = '-blocked_from';

    public function __construct(protected ScheduleBlockService $blocks) {}

    public static function model(): string
    {
        return ScheduleBlock::class;
    }

    public function fields(): array
    {
        return ['id', 'practitioner_id', 'location_id', 'department_id', 'resource_reference', 'reason', 'blocked_from', 'blocked_to', 'created_at'];
    }

    public function filters(): array
    {
        return ['practitioner_id' => 'practitioner_id', 'location_id' => 'location_id'];
    }

    public function sortable(): array
    {
        return ['blocked_from', 'created_at'];
    }

    public function storeRules(): array
    {
        return [
            'practitioner_id' => ['nullable', 'uuid'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'resource_reference' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:255'],
            'blocked_from' => ['required', 'date'],
            'blocked_to' => ['required', 'date', 'after:blocked_from'],
        ];
    }

    protected function createRecord(array $data): Model
    {
        return $this->blocks->create($data);
    }
}
