<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScheduleBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('branch_id')
                    ->required(),
                TextInput::make('practitioner_id')
                    ->default(null),
                TextInput::make('location_id')
                    ->default(null),
                TextInput::make('department_id')
                    ->default(null),
                TextInput::make('resource_reference')
                    ->default(null),
                TextInput::make('reason')
                    ->default(null),
                DateTimePicker::make('blocked_from')
                    ->required(),
                DateTimePicker::make('blocked_to')
                    ->required(),
            ]);
    }
}
