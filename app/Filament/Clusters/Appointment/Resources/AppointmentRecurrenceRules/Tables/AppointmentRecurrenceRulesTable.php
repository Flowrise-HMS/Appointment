<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Actions\RecurrenceRuleActions;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\AppointmentRecurrenceRuleResource;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Schemas\AppointmentRecurrenceRuleForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Schemas\AppointmentRecurrenceRuleInfolist;
use Modules\Appointment\Models\AppointmentRecurrenceRule;
use Modules\Core\Support\SuperAdmin;

class AppointmentRecurrenceRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('appointment.patient'))
            ->columns([
                TextColumn::make('appointment.start_at')
                    ->label(__('Parent appointment'))
                    ->state(fn (AppointmentRecurrenceRule $record): string => $record->appointment
                        ? AppointmentRecurrenceRuleForm::appointmentLabel($record->appointment)
                        : '—'),
                TextColumn::make('frequency')
                    ->label(__('Pattern'))
                    ->state(fn (AppointmentRecurrenceRule $record): string => AppointmentRecurrenceRuleInfolist::describe($record))
                    ->sortable(),
                TextColumn::make('occurrence_count')
                    ->label(__('Occurrences'))
                    ->placeholder('—'),
                TextColumn::make('until_at')
                    ->label(__('Until'))
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Active')),
            ])
            ->recordActions([
                Action::make('activities')
                    ->visible(fn (): bool => SuperAdmin::check())
                    ->label('Activities')
                    ->icon('heroicon-o-bell-alert')
                    ->url(fn ($record) => AppointmentRecurrenceRuleResource::getUrl('activities', ['record' => $record])),
                ViewAction::make(),
                EditAction::make()
                    // Only runs for modal edits (relation manager); the edit page expands in afterSave().
                    ->after(fn (AppointmentRecurrenceRule $record) => RecurrenceRuleActions::expandAndNotify($record)),
                RecurrenceRuleActions::generate(),
                RecurrenceRuleActions::cancelSeries(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
