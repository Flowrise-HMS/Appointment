<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\AppointmentCluster;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages\CreateAppointmentParticipant;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages\EditAppointmentParticipant;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages\ListAppointmentParticipants;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages\ViewAppointmentParticipant;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Schemas\AppointmentParticipantForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Schemas\AppointmentParticipantInfolist;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Tables\AppointmentParticipantsTable;
use Modules\Appointment\Models\AppointmentParticipant;

class AppointmentParticipantResource extends Resource
{
    protected static ?string $model = AppointmentParticipant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = AppointmentCluster::class;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return AppointmentParticipantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppointmentParticipantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentParticipantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppointmentParticipants::route('/'),
            'create' => CreateAppointmentParticipant::route('/create'),
            'view' => ViewAppointmentParticipant::route('/{record}'),
            'edit' => EditAppointmentParticipant::route('/{record}/edit'),
        ];
    }
}
