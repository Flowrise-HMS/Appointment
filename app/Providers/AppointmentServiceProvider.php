<?php

namespace Modules\Appointment\Providers;

use Filament\Pages\Page;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Appointment\Classes\Actions\ClinicalActions;
use Modules\Appointment\Classes\Services\FhirAppointmentTransformer;
use Modules\Appointment\Classes\Services\SiuMessageAdapter;
use Modules\Appointment\Contracts\FhirAppointmentTransformerContract;
use Modules\Appointment\Contracts\SiuMessageAdapterContract;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Policies\AppointmentPolicy;
use Modules\Core\Classes\Support\PageHeaderActionsRegistry;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AppointmentServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Appointment';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'appointment';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(FhirAppointmentTransformerContract::class, FhirAppointmentTransformer::class);
        $this->app->bind(SiuMessageAdapterContract::class, SiuMessageAdapter::class);
    }

    public function boot(): void
    {
        parent::boot();

        Gate::policy(Appointment::class, AppointmentPolicy::class);

        $this->registerClinicalWorkspacePatientPageHeaderActions();
    }

    protected function registerClinicalWorkspacePatientPageHeaderActions(): void
    {
        if (! $this->app->bound(PageHeaderActionsRegistry::class)) {
            return;
        }

        if (! Module::isEnabled('Clinical')) {
            return;
        }

        $registry = $this->app->make(PageHeaderActionsRegistry::class);
        $workspacePatientsPage = 'Modules\\Clinical\\Filament\\Clusters\\Workspace\\Pages\\Patients';
        $workspaceMyAgendaPage = 'Modules\\Clinical\\Filament\\Clusters\\Workspace\\Pages\\MyAgenda';
        $timelinePage = 'Modules\\Clinical\\Filament\\Clusters\\Workspace\\Pages\\Timeline';
        $patientProfilePage = 'Modules\\Clinical\\Filament\\Clusters\\Workspace\\Pages\\PatientProfile';

        $workspaceHeaderFactory = fn (Page $page): array => ClinicalActions::make()->workspaceHeaderActions($page);

        if (class_exists($workspacePatientsPage)) {
            $registry->register($workspacePatientsPage, $workspaceHeaderFactory);
        }

        if (class_exists($workspaceMyAgendaPage)) {
            $registry->register($workspaceMyAgendaPage, $workspaceHeaderFactory);
        }

        if (class_exists($timelinePage)) {
            $registry->register($timelinePage, $workspaceHeaderFactory);
        }

        if (class_exists($patientProfilePage)) {
            $registry->register($patientProfilePage, $workspaceHeaderFactory);
        }
    }

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
