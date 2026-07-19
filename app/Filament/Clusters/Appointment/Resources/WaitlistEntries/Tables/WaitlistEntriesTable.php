<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Appointment\Enums\WaitlistEntryStatus;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\WaitlistEntryResource;

class WaitlistEntriesTable
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
                TextColumn::make('patient.title')
                    ->searchable(),
                TextColumn::make('preferredPractitioner.display_name')
                    ->label(__('Preferred practitioner'))
                    ->placeholder('—')
                    ->searchable(query: function ($query, string $search): void {
                        $query->whereHas('preferredPractitioner', function ($q) use ($search): void {
                            $q->where('staff_number', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('preferredLocation.name')
                    ->searchable(),
                TextColumn::make('preferredDepartment.name')
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
                SelectFilter::make('branch_id')
                    ->relationship('branch', 'name'),
                SelectFilter::make('status')
                    ->options(WaitlistEntryStatus::class),
            ])
            ->recordActions([
                Action::make('activities')
                    ->label('Activities')
                    ->icon('heroicon-o-bell-alert')
                    ->url(fn ($record) => WaitlistEntryResource::getUrl('activities', ['record' => $record])),
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
