<?php

namespace Modules\Appointment\Filament\Clusters\Appointment;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Override;

class AppointmentCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Appointment';

    #[Override]
    public function getWidgetData(): array
    {
        return [
            // DispensingQueue
        ];
    }
}
