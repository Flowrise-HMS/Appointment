<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\AppointmentCluster;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages\CreateAppointmentRecurrenceRule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages\EditAppointmentRecurrenceRule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages\ListAppointmentRecurrenceRules;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages\ViewAppointmentRecurrenceRule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Schemas\AppointmentRecurrenceRuleForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Schemas\AppointmentRecurrenceRuleInfolist;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Tables\AppointmentRecurrenceRulesTable;
use Modules\Appointment\Models\AppointmentRecurrenceRule;

class AppointmentRecurrenceRuleResource extends Resource
{
    protected static ?string $model = AppointmentRecurrenceRule::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = AppointmentCluster::class;

    public static function form(Schema $schema): Schema
    {
        return AppointmentRecurrenceRuleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppointmentRecurrenceRuleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentRecurrenceRulesTable::configure($table);
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
            'index' => ListAppointmentRecurrenceRules::route('/'),
            'create' => CreateAppointmentRecurrenceRule::route('/create'),
            'view' => ViewAppointmentRecurrenceRule::route('/{record}'),
            'edit' => EditAppointmentRecurrenceRule::route('/{record}/edit'),
        ];
    }
}
