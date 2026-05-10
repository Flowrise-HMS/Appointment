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
                Select::make('branch_id')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),
                Select::make('patient_id')
                    ->label(__('Patient'))
                    ->relationship('patient', 'mrn')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record?->full_name)
                    ->preload()
                    ->searchable()
                    ->required(),
                Select::make('preferred_practitioner_id')
                    ->label(__('Preferred Practitioner'))
                    ->relationship('preferredPractitioner', 'staff_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record?->display_name)
                    ->preload()
                    ->searchable()
                    ->default(null),
                Select::make('preferred_location_id')
                    ->label(__('Preferred Location'))
                    ->relationship('preferredLocation', 'name')
                    ->preload()
                    ->searchable()
                    ->default(null),
                Select::make('preferred_department_id')
                    ->label(__('Preferred Department'))
                    ->relationship('preferredDepartment', 'name')
                    ->preload()
                    ->searchable()
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
