<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Appointment\Enums\AppointmentParticipantStatus;

class AppointmentParticipantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Participant'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('participant_type')
                                    ->label(__('Participant type'))
                                    ->required()
                                    ->maxLength(64)
                                    ->default('practitioner')
                                    ->helperText(__('FHIR-style role, e.g. practitioner, patient, related-person.')),
                                TextInput::make('participant_type_code')
                                    ->label(__('Type code'))
                                    ->maxLength(64),
                            ]),
                        TextInput::make('actor_reference')
                            ->label(__('Actor reference'))
                            ->required()
                            ->maxLength(36)
                            ->helperText(__('UUID or stable identifier for the participant.')),
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label(__('Status'))
                                    ->options(AppointmentParticipantStatus::class)
                                    ->default(AppointmentParticipantStatus::NEEDS_ACTION)
                                    ->required(),
                                Toggle::make('required')
                                    ->label(__('Required for this visit'))
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }
}
