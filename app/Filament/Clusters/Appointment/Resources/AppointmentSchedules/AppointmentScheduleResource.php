<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\AppointmentCluster;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages\CreateAppointmentSchedule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages\EditAppointmentSchedule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages\ListAppointmentSchedules;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages\ViewAppointmentSchedule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\RelationManagers\SlotsRelationManager;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Schemas\AppointmentScheduleForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Schemas\AppointmentScheduleInfolist;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Tables\AppointmentSchedulesTable;
use Modules\Appointment\Models\AppointmentSchedule;
use Modules\Core\Enums\NavigationGroup;

/**
 * Practitioner / service availability schedules and their generated slots.
 */
class AppointmentScheduleResource extends Resource
{
    protected static ?string $model = AppointmentSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::APPOINTMENTS;

    protected static ?string $cluster = AppointmentCluster::class;

    protected static ?string $modelLabel = 'schedule';

    protected static ?string $navigationLabel = 'Schedules';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AppointmentScheduleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppointmentScheduleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentSchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SlotsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppointmentSchedules::route('/'),
            'create' => CreateAppointmentSchedule::route('/create'),
            'view' => ViewAppointmentSchedule::route('/{record}'),
            'edit' => EditAppointmentSchedule::route('/{record}/edit'),
        ];
    }
}
