<?php

namespace Modules\Appointment\Classes\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Modules\Appointment\Classes\Services\AppointmentSchedulingService;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\AppointmentResource;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Schemas\AppointmentForm;
use Modules\Appointment\Models\Appointment;
use Modules\Patient\Models\Patient;

/**
 * Filament actions contributed to Clinical workspace pages (via PageHeaderActionsRegistry).
 * Mirrors the instance + service + slideOver pattern used in {@see PatientActions}.
 */
final class ClinicalActions
{
    protected const WORKSPACE_PATIENTS_PAGE = 'Modules\\Clinical\\Filament\\Clusters\\Workspace\\Pages\\Patients';

    protected const WORKSPACE_MY_AGENDA_PAGE = 'Modules\\Clinical\\Filament\\Clusters\\Workspace\\Pages\\MyAgenda';

    protected ?Patient $patient = null;

    public function __construct(
        protected AppointmentSchedulingService $schedulingService,
    ) {}

    public static function make(): static
    {
        return new self(app(AppointmentSchedulingService::class));
    }

    public function forPatient(?Patient $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function fromPage(Page $page): static
    {
        $patient = data_get($page, 'currentPatient');

        if ($patient instanceof Patient) {
            return $this->forPatient($patient);
        }

        $patientId = data_get($page, 'patientId');
        if ($patientId) {
            return $this->forPatient(Patient::query()->find($patientId));
        }

        return $this->forPatient(null);
    }

    /**
     * @return array<int, Action>
     */
    public function workspaceHeaderActions(Page $page): array
    {
        if ($page::class === self::WORKSPACE_PATIENTS_PAGE || $page::class === self::WORKSPACE_MY_AGENDA_PAGE) {
            return [$this->quickCreateAppointmentFromWorkspaceAction($page)];
        }

        $this->fromPage($page);

        return [$this->scheduleAppointmentAction()];
    }

    protected function quickCreateAppointmentFromWorkspaceAction(Page $page): Action
    {
        return Action::make('appointment.quick_create')
            ->label(__('Add appointment'))
            ->icon('heroicon-m-plus')
            ->color('primary')
            ->model(Appointment::class)
            ->slideOver()
            ->schema(fn (Schema $schema): Schema => AppointmentForm::workspaceQuickCreate(
                $schema,
                Auth::user()?->branch_id,
            ))
            ->mutateDataUsing(fn (array $data): array => $this->injectWorkspaceQuickData($data))
            ->action(function (array $data) use ($page): void {
                $appointment = $this->schedulingService->schedule($data);
                $viewUrl = AppointmentResource::getUrl('view', ['record' => $appointment]);

                Notification::make()
                    ->title(__('Appointment scheduled'))
                    ->success()
                    ->when(
                        filled($viewUrl),
                        fn (Notification $n) => $n->actions([
                            NotificationAction::make('open')
                                ->label(__('Open record'))
                                ->url($viewUrl),
                        ])
                    )
                    ->send();

                $page->dispatch('refresh-workspace-appointments');
            })
            ->visible(fn (): bool => Auth::check() && Gate::allows('create', Appointment::class));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function injectWorkspaceQuickData(array $data): array
    {
        $userId = Auth::id();
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;
        $data['branch_id'] = $data['branch_id'] ?? Auth::user()?->branch_id;

        return $data;
    }

    protected function scheduleAppointmentAction(): Action
    {
        return Action::make('appointment.schedule')
            ->label(__('Schedule appointment'))
            ->icon('heroicon-m-calendar-days')
            ->color('primary')
            ->model(Appointment::class)
            ->slideOver()
            ->schema(fn (Schema $schema): Schema => AppointmentForm::configure(
                $schema,
                hidePatient: true,
                defaultBranchId: $this->patient?->branch_id,
                includeExtendedContext: false,
            ))
            ->mutateDataUsing(fn (array $data): array => $this->injectAppointmentData($data))
            ->action(fn (array $data) => $this->schedulingService->schedule($data))
            ->successNotificationTitle(__('Appointment scheduled'))
            ->visible(fn (): bool => Auth::check()
                && $this->patient !== null
                && Gate::allows('create', Appointment::class));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function injectAppointmentData(array $data): array
    {
        if ($this->patient) {
            $data['patient_id'] = $this->patient->id;
            $data['branch_id'] = $data['branch_id'] ?? $this->patient->branch_id;
        }

        $userId = Auth::id();
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        return $data;
    }
}
