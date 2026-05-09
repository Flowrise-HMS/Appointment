<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Schemas\AppointmentRecurrenceRuleForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Schemas\AppointmentRecurrenceRuleInfolist;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Tables\AppointmentRecurrenceRulesTable;

class RecurrenceRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'recurrenceRules';

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
