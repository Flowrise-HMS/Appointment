<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Schemas;

use Filament\Schemas\Schema;

class AppointmentSyncOutboxInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
