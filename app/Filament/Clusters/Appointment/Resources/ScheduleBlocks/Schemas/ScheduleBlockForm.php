<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\Core\Classes\Services\BranchService;

class ScheduleBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('branch_id')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->default(app(BranchService::class)->getDefaultBranchId())
                    ->required(),
                Select::make('practitioner_id')
                    ->label(__('Practitioner'))
                    ->relationship('practitioner', 'staff_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record?->display_name)
                    ->searchable()
                    ->default(null),
                Select::make('location_id')
                    ->label(__('Location'))
                    ->relationship('location', 'name')
                    ->searchable()
                    ->default(null),
                Select::make('department_id')
                    ->label(__('Department'))
                    ->relationship('department', 'name')
                    ->searchable()
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
