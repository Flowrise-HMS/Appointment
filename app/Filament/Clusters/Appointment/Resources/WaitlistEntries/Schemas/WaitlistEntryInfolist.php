<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WaitlistEntryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('branch.name'),
                TextEntry::make('patient.full_name')
                    ->label('Patient'),
                TextEntry::make('preferredPractitioner.display_name')
                    ->placeholder('-'),
                TextEntry::make('preferredLocation.name')
                    ->placeholder('-'),
                TextEntry::make('preferredDepartment.name')
                    ->placeholder('-'),
                TextEntry::make('urgency_score')
                    ->numeric(),
                TextEntry::make('wait_time_score')
                    ->numeric(),
                TextEntry::make('referral_score')
                    ->numeric(),
                TextEntry::make('manual_override_score')
                    ->numeric(),
                TextEntry::make('computed_priority_score')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
