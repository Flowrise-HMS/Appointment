<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Modules\Appointment\Filament\Clusters\Appointment\AppointmentCluster;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\CreateAppointment;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\EditAppointment;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\ListAppointments;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\ViewAppointment;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\RelationManagers\AppointmentAuditsRelationManager;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\RelationManagers\ParticipantsRelationManager;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\RelationManagers\RecurrenceRulesRelationManager;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\RelationManagers\ResourcesRelationManager;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Schemas\AppointmentForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Schemas\AppointmentInfolist;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Tables\AppointmentsTable;
use Modules\Appointment\Models\Appointment;
use Modules\Core\Enums\NavigationGroup;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::APPOINTMENTS;

    protected static ?string $cluster = AppointmentCluster::class;

    public static function form(Schema $schema): Schema
    {
        return AppointmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppointmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ParticipantsRelationManager::class,
            AppointmentAuditsRelationManager::class,
            RecurrenceRulesRelationManager::class,
            ResourcesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppointments::route('/'),
            'create' => CreateAppointment::route('/create'),
            'view' => ViewAppointment::route('/{record}'),
            'edit' => EditAppointment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
