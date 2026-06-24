<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\AppointmentResource;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')->rowIndex(),
                TextColumn::make('patient.mrn')
                    ->label('Patient')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('practitioner_primary_id')
                    ->label('Practitioner')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('start_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?AppointmentStatus $state): ?string => $state?->getLabel())
                    ->color(fn (?AppointmentStatus $state): string|array|null => $state?->getColor() ?? 'gray')
                    ->sortable(),
                TextColumn::make('priority')
                    ->badge()
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->toggleable(),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('service.name')
                    ->label('Service')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('coverage_type')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(AppointmentStatus::class),
                SelectFilter::make('branch_id')
                    ->relationship('branch', 'name'),
                SelectFilter::make('department_id')
                    ->relationship('department', 'name'),
                TernaryFilter::make('checked_in_at')
                    ->label('Checked-in')
                    ->nullable(),
                Filter::make('today')
                    ->query(fn (Builder $query) => $query->whereDate('start_at', now()->toDateString())),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('activities')
                    ->label('Activities')
                    ->icon('heroicon-o-bell-alert')
                    ->url(fn ($record) => AppointmentResource::getUrl('activities', ['record' => $record])),
                Action::make('checkIn')
                    ->icon('heroicon-m-arrow-right-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status instanceof AppointmentStatus && $record->status->allowsCheckIn())
                    ->action(fn ($record) => $record->update([
                        'status' => AppointmentStatus::ARRIVED,
                        'checked_in_at' => now(),
                        'version' => $record->version + 1,
                    ])),
                Action::make('cancel')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status instanceof AppointmentStatus && $record->status->allowsCancellation())
                    ->action(fn ($record) => $record->update([
                        'status' => AppointmentStatus::CANCELLED,
                        'cancellation_reason_code' => 'MANUAL_CANCEL',
                        'version' => $record->version + 1,
                    ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('bulkCancel')
                        ->label('Bulk Cancel')
                        ->icon('heroicon-m-x-mark')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update([
                            'status' => AppointmentStatus::CANCELLED,
                            'cancellation_reason_code' => 'BULK_CANCEL',
                            'version' => $record->version + 1,
                        ]))),
                ]),
            ]);
    }
}
