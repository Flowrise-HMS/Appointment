<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScheduleBlocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('branch_id')
                    ->searchable(),
                TextColumn::make('practitioner_id')
                    ->searchable(),
                TextColumn::make('location_id')
                    ->searchable(),
                TextColumn::make('department_id')
                    ->searchable(),
                TextColumn::make('resource_reference')
                    ->searchable(),
                TextColumn::make('reason')
                    ->searchable(),
                TextColumn::make('blocked_from')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('blocked_to')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
