<?php

namespace Modules\Appointment\Http\Controllers\Api\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Appointment\Classes\Services\SlotGenerationService;
use Modules\Appointment\Models\AppointmentSchedule;
use Modules\Appointment\Models\ScheduleAvailability;
use Modules\Core\Http\Controllers\Api\ResourceApiController;

/**
 * @group Appointments
 *
 * Availability schedules with weekly hours. Saving regenerates the schedule's
 * bookable slots.
 */
class AppointmentScheduleController extends ResourceApiController
{
    protected array $with = ['availabilities'];

    protected string $defaultSort = 'name';

    public function __construct(protected SlotGenerationService $slots) {}

    public static function model(): string
    {
        return AppointmentSchedule::class;
    }

    public static function label(): string
    {
        return 'schedule';
    }

    public function fields(): array
    {
        return ['id', 'name', 'practitioner_id', 'service_id', 'location_id', 'department_id', 'slot_minutes', 'generation_days',
            'planning_horizon_start', 'planning_horizon_end', 'timezone', 'is_active', 'comment', 'created_at', 'updated_at'];
    }

    public function filters(): array
    {
        return ['practitioner_id' => 'practitioner_id', 'service_id' => 'service_id', 'is_active' => 'is_active'];
    }

    public function searchable(): array
    {
        return ['name'];
    }

    public function sortable(): array
    {
        return ['name', 'created_at'];
    }

    public function storeRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'practitioner_id' => ['nullable', 'uuid'],
            'service_id' => ['nullable', 'uuid', 'exists:services,id'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'slot_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'generation_days' => ['nullable', 'integer', 'min:1', 'max:180'],
            'planning_horizon_start' => ['nullable', 'date'],
            'planning_horizon_end' => ['nullable', 'date', 'after_or_equal:planning_horizon_start'],
            'timezone' => ['nullable', 'timezone:all'],
            'is_active' => ['nullable', 'boolean'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'availabilities' => ['nullable', 'array', 'max:50'],
            'availabilities.*.weekday' => ['required', 'integer', 'between:1,7'],
            'availabilities.*.start_time' => ['required', 'date_format:H:i'],
            'availabilities.*.end_time' => ['required', 'date_format:H:i', 'after:availabilities.*.start_time'],
        ];
    }

    protected function createRecord(array $data): Model
    {
        return DB::transaction(function () use ($data): AppointmentSchedule {
            $schedule = AppointmentSchedule::query()->create(collect($data)->except('availabilities')->all());
            $this->syncAvailabilities($schedule, $data['availabilities'] ?? []);
            $this->slots->generate($schedule->refresh());

            return $schedule;
        });
    }

    protected function updateRecord(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data): Model {
            $record->update(collect($data)->except('availabilities')->all());

            if (array_key_exists('availabilities', $data)) {
                $this->syncAvailabilities($record, $data['availabilities'] ?? []);
            }

            $this->slots->generate($record->refresh());

            return $record;
        });
    }

    /**
     * @param  list<array{weekday: int, start_time: string, end_time: string}>  $windows
     */
    private function syncAvailabilities(AppointmentSchedule $schedule, array $windows): void
    {
        $schedule->availabilities()->delete();

        foreach ($windows as $window) {
            $schedule->availabilities()->create($window);
        }
    }

    protected function present(Model $record, Request $request): array
    {
        return [
            ...parent::present($record, $request),
            'availabilities' => $record->availabilities->map(fn (ScheduleAvailability $window): array => [
                'weekday' => $window->weekday,
                'start_time' => substr((string) $window->start_time, 0, 5),
                'end_time' => substr((string) $window->end_time, 0, 5),
            ])->values()->all(),
        ];
    }

    /**
     * Regenerate the schedule's slots now.
     */
    public function generateSlots(string $id): JsonResponse
    {
        return $this->act($id, 'update', function (AppointmentSchedule $schedule): AppointmentSchedule {
            $this->slots->generate($schedule);

            return $schedule;
        });
    }
}
