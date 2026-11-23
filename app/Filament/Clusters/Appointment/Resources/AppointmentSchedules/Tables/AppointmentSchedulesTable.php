<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Actions\GenerateSlotsAction;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Schemas\AppointmentScheduleInfolist;
use Modules\Appointment\Models\AppointmentSchedule;
use Modules\Core\Support\ModuleAvailability;

class AppointmentSchedulesTable
{
    public static function configure(Table $table): Table
    {
        $staffEnabled = ModuleAvailability::staffEnabled();

        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(array_values(array_filter([
                'availabilities',
                'service',
                $staffEnabled ? 'practitioner' : null,
            ]))))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('practitioner.display_name')
                    ->label(__('Practitioner'))
                    ->placeholder('—')
                    ->visible($staffEnabled),
                TextColumn::make('service.name')
                    ->label(__('Service'))
                    ->placeholder('—'),
                TextColumn::make('hours')
                    ->label(__('Weekly hours'))
                    ->state(fn (AppointmentSchedule $record): string => AppointmentScheduleInfolist::hours($record))
                    ->wrap(),
                TextColumn::make('slot_minutes')
                    ->label(__('Slot'))
                    ->suffix(' min')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_active')->label(__('Active')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                GenerateSlotsAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
