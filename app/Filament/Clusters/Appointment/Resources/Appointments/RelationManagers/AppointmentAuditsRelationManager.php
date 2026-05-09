<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Schemas\AppointmentAuditForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Schemas\AppointmentAuditInfolist;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Tables\AppointmentAuditsTable;

class AppointmentAuditsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointmentAudits';

    public function form(Schema $schema): Schema
    {
        return AppointmentAuditForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return AppointmentAuditInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return AppointmentAuditsTable::configure($table);
    }
}
