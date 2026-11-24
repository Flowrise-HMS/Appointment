<?php

namespace Modules\Appointment\Http\Controllers\Api\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Appointment\Classes\Services\RecurrenceExpansionService;
use Modules\Appointment\Enums\RecurrenceFrequency;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentRecurrenceRule;
use Modules\Core\Http\Controllers\Api\ResourceApiController;

/**
 * @group Appointments
 *
 * Recurrence rules on appointments. Creating or extending a rule books its
 * occurrences; the response includes what was created and skipped.
 */
class AppointmentRecurrenceRuleController extends ResourceApiController
{
    /** @var array{created: list<string>, existing: list<string>, conflicts: list<string>}|null */
    private ?array $lastExpansion = null;

    public function __construct(protected RecurrenceExpansionService $expansion) {}

    public static function model(): string
    {
        return AppointmentRecurrenceRule::class;
    }

    public static function label(): string
    {
        return 'recurrence rule';
    }

    public function fields(): array
    {
        return ['id', 'appointment_id', 'frequency', 'interval', 'by_day', 'occurrence_count', 'until_at', 'timezone', 'is_active', 'created_at'];
    }

    public function filters(): array
    {
        return ['appointment_id' => 'appointment_id', 'is_active' => 'is_active'];
    }

    public function storeRules(): array
    {
        return [
            'appointment_id' => ['required', 'uuid'],
            'frequency' => ['required', Rule::enum(RecurrenceFrequency::class)],
            'interval' => ['nullable', 'integer', 'min:1', 'max:52'],
            'by_day' => ['nullable', 'array'],
            'by_day.*' => [Rule::in(array_keys(RecurrenceFrequency::weekdayCodes()))],
            'occurrence_count' => ['required_without:until_at', 'nullable', 'integer', 'min:1', 'max:104'],
            'until_at' => ['required_without:occurrence_count', 'nullable', 'date'],
            'timezone' => ['nullable', 'timezone:all'],
        ];
    }

    public function updateRules(?Model $record = null): array
    {
        return collect(parent::updateRules($record))->except('appointment_id')->all();
    }

    protected function createRecord(array $data): Model
    {
        Appointment::query()->findOrFail($data['appointment_id']);
        $rule = AppointmentRecurrenceRule::query()->create($data + ['interval' => 1]);
        $this->expand($rule);

        return $rule;
    }

    protected function updateRecord(Model $record, array $data): Model
    {
        $record->update($data);
        $this->expand($record);

        return $record;
    }

    protected function present(Model $record, Request $request): array
    {
        return [
            ...parent::present($record, $request),
            'generated_count' => $record->generatedAppointments()->count(),
            'last_expansion' => $this->lastExpansion,
        ];
    }

    private function expand(AppointmentRecurrenceRule $rule): void
    {
        $result = $this->expansion->expandWithReport($rule->refresh());

        $this->lastExpansion = [
            'created' => $result->created->map(fn (Appointment $appointment): string => (string) $appointment->id)->values()->all(),
            'existing' => $result->existing,
            'conflicts' => $result->conflicts,
        ];
    }

    /**
     * Book any missing occurrences (idempotent).
     */
    public function generate(string $id): JsonResponse
    {
        return $this->act($id, 'update', function (AppointmentRecurrenceRule $rule): AppointmentRecurrenceRule {
            $this->expand($rule);

            return $rule;
        });
    }

    /**
     * Stop the rule and cancel its future occurrences.
     */
    public function cancelSeries(string $id): JsonResponse
    {
        return $this->act($id, 'update', function (AppointmentRecurrenceRule $rule): AppointmentRecurrenceRule {
            $this->expansion->cancelSeries($rule);

            return $rule;
        });
    }
}
