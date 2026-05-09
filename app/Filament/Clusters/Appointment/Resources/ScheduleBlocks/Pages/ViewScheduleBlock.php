<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\ScheduleBlockResource;

class ViewScheduleBlock extends ViewRecord
{
    protected static string $resource = ScheduleBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
