<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\AppointmentAuditResource;

class AppointmentAuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('activities')
                    ->label('Activities')
                    ->icon('heroicon-o-bell-alert')
                    ->url(fn ($record) => AppointmentAuditResource::getUrl('activities', ['record' => $record])),
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
