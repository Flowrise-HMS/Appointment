<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\AppointmentParticipantResource;

class AppointmentParticipantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('participant_type')
                    ->label(__('Type'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('participant_type_code')
                    ->label(__('Code'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('actor_reference')
                    ->label(__('Actor'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                IconColumn::make('required')
                    ->label(__('Req.'))
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('activities')
                    ->label('Activities')
                    ->icon('heroicon-o-bell-alert')
                    ->url(fn ($record) => AppointmentParticipantResource::getUrl('activities', ['record' => $record])),
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
