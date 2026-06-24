<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\AppointmentType;

class AppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Appointment Summary')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('patient.mrn')->label('Patient MRN')->placeholder('-'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->formatStateUsing(fn (?AppointmentStatus $state): ?string => $state?->getLabel())
                                    ->color(fn (?AppointmentStatus $state): string|array|null => $state?->getColor() ?? 'gray'),
                                TextEntry::make('priority')->badge(),
                                TextEntry::make('appointment_type')
                                    ->placeholder('-')
                                    ->formatStateUsing(fn (?AppointmentType $state): ?string => $state?->getLabel()),
                                TextEntry::make('start_at')->dateTime()->placeholder('-'),
                                TextEntry::make('end_at')->dateTime()->placeholder('-'),
                            ]),
                    ]),
                Section::make('Clinical and Service Context')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('service_category_code')->placeholder('-'),
                                TextEntry::make('service_type_code')->placeholder('-'),
                                TextEntry::make('service.name')->label('Service')->placeholder('-'),
                                TextEntry::make('coverage_type')->placeholder('-'),
                                TextEntry::make('reason_code')->placeholder('-'),
                                TextEntry::make('reason_text')->placeholder('-'),
                                TextEntry::make('checked_in_at')->dateTime()->placeholder('-'),
                                TextEntry::make('completed_at')->dateTime()->placeholder('-'),
                            ]),
                        TextEntry::make('notes_encrypted')
                            ->label('Protected Notes')
                            ->placeholder('-'),
                    ]),
                Section::make('Metadata')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('version')->placeholder('-'),
                                TextEntry::make('created_at')->dateTime()->placeholder('-'),
                                TextEntry::make('updated_at')->dateTime()->placeholder('-'),
                            ]),
                    ]),
            ]);
    }
}
