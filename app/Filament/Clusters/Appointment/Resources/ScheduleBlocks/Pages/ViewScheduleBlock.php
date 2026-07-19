<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\ScheduleBlockResource;

class ViewScheduleBlock extends ViewRecord
{
    protected static string $resource = ScheduleBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activities')
                ->label('Activities')
                ->icon('heroicon-o-bell-alert')
                ->url(fn () => ScheduleBlockResource::getUrl('activities', ['record' => $this->getRecord()])),
            EditAction::make(),
        ];
    }
}
