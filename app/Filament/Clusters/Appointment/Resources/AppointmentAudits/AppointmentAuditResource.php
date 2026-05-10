<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\AppointmentCluster;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages\CreateAppointmentAudit;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages\EditAppointmentAudit;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages\ListAppointmentAudits;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages\ViewAppointmentAudit;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Schemas\AppointmentAuditForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Schemas\AppointmentAuditInfolist;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Tables\AppointmentAuditsTable;
use Modules\Appointment\Models\AppointmentAudit;

class AppointmentAuditResource extends Resource
{
    protected static ?string $model = AppointmentAudit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = AppointmentCluster::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return AppointmentAuditForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppointmentAuditInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentAuditsTable::configure($table);
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
            'index' => ListAppointmentAudits::route('/'),
            'create' => CreateAppointmentAudit::route('/create'),
            'view' => ViewAppointmentAudit::route('/{record}'),
            'edit' => EditAppointmentAudit::route('/{record}/edit'),
        ];
    }
}
