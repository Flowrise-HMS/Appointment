<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\ScheduleBlockResource;

class CreateScheduleBlock extends CreateRecord
{
    protected static string $resource = ScheduleBlockResource::class;
}
