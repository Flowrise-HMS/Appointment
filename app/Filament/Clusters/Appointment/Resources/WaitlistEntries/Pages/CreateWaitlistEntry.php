<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\WaitlistEntryResource;

class CreateWaitlistEntry extends CreateRecord
{
    protected static string $resource = WaitlistEntryResource::class;
}
