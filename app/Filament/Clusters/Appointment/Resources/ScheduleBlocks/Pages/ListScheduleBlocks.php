<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\ScheduleBlockResource;

class ListScheduleBlocks extends ListRecords
{
    protected static string $resource = ScheduleBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
