<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Modules\Appointment\Filament\Clusters\Appointment\AppointmentCluster;
use Modules\Appointment\Filament\Widgets\AppointmentsCalendar;
use Modules\Core\Enums\NavigationGroup;
use Filament\Support\Icons\Heroicon;


class Calendar extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected string $view = 'appointment::filament.clusters.appointment.pages.calendar';

    protected static ?string $cluster = AppointmentCluster::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::APPOINTMENTS;

    protected function getHeaderWidgets(): array
    {
        return [
            AppointmentsCalendar::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
