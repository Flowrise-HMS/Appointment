<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Appointment\Enums\AppointmentParticipantStatus;

class AppointmentParticipantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Participant'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('participant_type')->placeholder('—'),
                                TextEntry::make('participant_type_code')->placeholder('—'),
                                TextEntry::make('actor_reference')->placeholder('—'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->formatStateUsing(fn (?AppointmentParticipantStatus $state): ?string => $state?->getLabel()),
                                TextEntry::make('required')
                                    ->formatStateUsing(fn (?bool $state): string => $state ? __('Yes') : __('No')),
                            ]),
                    ]),
            ]);
    }
}
