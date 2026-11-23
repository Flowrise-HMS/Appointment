<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Appointment\Enums\SlotStatus;

/**
 * Read-only list of the schedule's generated slots (upcoming by default).
 */
class SlotsRelationManager extends RelationManager
{
    protected static string $relationship = 'slots';

    protected static ?string $title = 'Slots';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('start_at')
                    ->label(__('Starts'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_at')
                    ->label(__('Ends'))
                    ->time(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('appointment_id')
                    ->label(__('Appointment'))
                    ->formatStateUsing(fn (?string $state): string => $state ? __('Booked') : '—')
                    ->placeholder('—'),
            ])
            ->defaultSort('start_at')
            ->filters([
                SelectFilter::make('status')
                    ->options(SlotStatus::class),
                Filter::make('upcoming')
                    ->label(__('Upcoming only'))
                    ->default()
                    ->query(fn (Builder $query): Builder => $query->where('start_at', '>=', now())),
            ]);
    }
}
