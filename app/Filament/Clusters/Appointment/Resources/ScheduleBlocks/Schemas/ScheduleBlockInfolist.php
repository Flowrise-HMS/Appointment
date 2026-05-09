<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ScheduleBlockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('branch_id'),
                TextEntry::make('practitioner_id')
                    ->placeholder('-'),
                TextEntry::make('location_id')
                    ->placeholder('-'),
                TextEntry::make('department_id')
                    ->placeholder('-'),
                TextEntry::make('resource_reference')
                    ->placeholder('-'),
                TextEntry::make('reason')
                    ->placeholder('-'),
                TextEntry::make('blocked_from')
                    ->dateTime(),
                TextEntry::make('blocked_to')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
