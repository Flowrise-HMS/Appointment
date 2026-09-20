<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Schemas\AppointmentRecurrenceRuleForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Schemas\AppointmentRecurrenceRuleInfolist;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Tables\AppointmentRecurrenceRulesTable;
use Modules\Appointment\Settings\AppointmentSettings;

class RecurrenceRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'recurrenceRules';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return app(AppointmentSettings::class)->recurrence_enabled
            && parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function form(Schema $schema): Schema
    {
        return AppointmentRecurrenceRuleForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return AppointmentRecurrenceRuleInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return AppointmentRecurrenceRulesTable::configure($table);
    }
}
