<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\Appointment\Enums\WaitlistEntryStatus;

class WaitlistEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('branch_id')
                    ->required(),
                Select::make('patient_id')
                    ->relationship('patient', 'title')
                    ->required(),
                TextInput::make('preferred_practitioner_id')
                    ->default(null),
                TextInput::make('preferred_location_id')
                    ->default(null),
                TextInput::make('preferred_department_id')
                    ->default(null),
                TextInput::make('urgency_score')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('wait_time_score')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('referral_score')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('manual_override_score')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('computed_priority_score')
                    ->required()
                    ->numeric()
                    ->default(3),
                Select::make('status')
                    ->options(WaitlistEntryStatus::class)
                    ->default('waiting')
                    ->required(),
            ]);
    }
}
