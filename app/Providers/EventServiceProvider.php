<?php

namespace Modules\Appointment\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Appointment\Listeners\CreateFollowUpAppointmentOnDischarge;
use Modules\Core\Support\ModuleAvailability;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [];

    /**
     * Listeners are registered explicitly (discovery only scans app/Listeners)
     * and Clinical events are wired softly, since Clinical is optional here.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = false;

    /**
     * @return array<class-string, array<int, class-string>>
     */
    public function listens(): array
    {
        $listen = $this->listen;

        if (ModuleAvailability::clinicalEnabled()) {
            $event = 'Modules\\Clinical\\Events\\PatientDischarged';

            if (class_exists($event) && class_exists(CreateFollowUpAppointmentOnDischarge::class)) {
                $listen[$event] = [CreateFollowUpAppointmentOnDischarge::class];
            }
        }

        return $listen;
    }

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
