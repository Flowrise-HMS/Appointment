<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\WaitlistEntryResource;

class ViewWaitlistEntry extends ViewRecord
{
    protected static string $resource = WaitlistEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
