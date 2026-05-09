<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WaitlistEntriesTable
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
                TextColumn::make('patient.title')
                    ->searchable(),
                TextColumn::make('preferred_practitioner_id')
                    ->searchable(),
                TextColumn::make('preferred_location_id')
                    ->searchable(),
                TextColumn::make('preferred_department_id')
                    ->searchable(),
                TextColumn::make('urgency_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('wait_time_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('referral_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('manual_override_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('computed_priority_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
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
