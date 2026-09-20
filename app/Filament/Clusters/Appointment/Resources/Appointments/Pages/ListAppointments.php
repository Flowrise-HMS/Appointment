<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Modules\Appointment\Filament\Clusters\Appointment\Pages\Calendar;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\AppointmentResource;
use Modules\Appointment\Filament\Exports\AppointmentExporter;
use Modules\Core\Filament\Support\SuperAdminExportAction;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calendar')
                ->label(__('Calendar'))
                ->icon(Heroicon::CalendarDays)
                ->color('gray')
                ->url(fn (): string => Calendar::getUrl())
                ->visible(fn (): bool => Calendar::canAccess()),
            SuperAdminExportAction::make(AppointmentExporter::class),
            CreateAction::make(),
        ];
    }
}
