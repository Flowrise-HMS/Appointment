<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\AppointmentCluster;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages\CreateAppointmentSyncOutbox;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages\EditAppointmentSyncOutbox;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages\ListAppointmentSyncOutboxes;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages\ViewAppointmentSyncOutbox;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Schemas\AppointmentSyncOutboxForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Schemas\AppointmentSyncOutboxInfolist;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Tables\AppointmentSyncOutboxesTable;
use Modules\Appointment\Models\AppointmentSyncOutbox;

class AppointmentSyncOutboxResource extends Resource
{
    protected static ?string $model = AppointmentSyncOutbox::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $cluster = AppointmentCluster::class;

    public static function form(Schema $schema): Schema
    {
        return AppointmentSyncOutboxForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppointmentSyncOutboxInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentSyncOutboxesTable::configure($table);
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
            'index' => ListAppointmentSyncOutboxes::route('/'),
            'create' => CreateAppointmentSyncOutbox::route('/create'),
            'view' => ViewAppointmentSyncOutbox::route('/{record}'),
            'edit' => EditAppointmentSyncOutbox::route('/{record}/edit'),
        ];
    }
}
