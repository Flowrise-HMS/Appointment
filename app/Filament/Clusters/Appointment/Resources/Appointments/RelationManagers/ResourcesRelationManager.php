<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\RelationManagers;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResourcesRelationManager extends RelationManager
{
    protected static string $relationship = 'resources';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Booked resource'))
                    ->schema([
                        TextInput::make('resource_type')
                            ->label(__('Resource type'))
                            ->required()
                            ->maxLength(64)
                            ->helperText(__('e.g. room, equipment, bed.')),
                        TextInput::make('resource_reference')
                            ->label(__('Resource reference'))
                            ->required()
                            ->maxLength(64)
                            ->helperText(__('Stable id for the asset (often UUID).')),
                        DateTimePicker::make('allocated_from')
                            ->label(__('From'))
                            ->seconds(false)
                            ->required()
                            ->native(false),
                        DateTimePicker::make('allocated_to')
                            ->label(__('To'))
                            ->seconds(false)
                            ->required()
                            ->native(false)
                            ->after('allocated_from'),
                    ]),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('resource_type')
                    ->label(__('Type'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('resource_reference')
                    ->label(__('Reference'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('allocated_from')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('allocated_to')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
