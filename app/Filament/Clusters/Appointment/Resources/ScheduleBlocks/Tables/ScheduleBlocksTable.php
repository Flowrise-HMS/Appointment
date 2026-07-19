<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\ScheduleBlockResource;

class ScheduleBlocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->searchable(),
                TextColumn::make('practitioner.name')
                    ->searchable(),
                TextColumn::make('location.name')
                    ->searchable(),
                TextColumn::make('department.name')
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
                SelectFilter::make('branch_id')
                    ->label(__('Branch'))
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('practitioner_id')
                    ->label(__('Practitioner'))
                    ->relationship('practitioner', 'staff_number')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record?->display_name)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('location_id')
                    ->label(__('Location'))
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('department_id')
                    ->label(__('Department'))
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('activities')
                    ->label('Activities')
                    ->icon('heroicon-o-bell-alert')
                    ->url(fn ($record) => ScheduleBlockResource::getUrl('activities', ['record' => $record])),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
