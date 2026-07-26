<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages;

use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\AppointmentSyncOutboxResource;
use Modules\Core\Filament\Pages\Concerns\RestrictsActivitiesToSuperAdmin;
use pxlrbt\FilamentActivityLog\Pages\ListActivitiesBySubject;

class ListAppointmentSyncOutboxActivities extends ListActivitiesBySubject
{
    use RestrictsActivitiesToSuperAdmin;

    protected static string $resource = AppointmentSyncOutboxResource::class;
}
