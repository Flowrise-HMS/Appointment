<?php

namespace Modules\Appointment\Filament\Clusters\Appointment;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Modules\Core\Enums\SidebarGroup;
use Override;

class AppointmentCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = SidebarGroup::PatientCare;

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Appointments';

    #[Override]
    public function getWidgetData(): array
    {
        return [
            // DispensingQueue
        ];
    }
}
