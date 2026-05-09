<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Schemas\AppointmentParticipantForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Schemas\AppointmentParticipantInfolist;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Tables\AppointmentParticipantsTable;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    public function form(Schema $schema): Schema
    {
        return AppointmentParticipantForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return AppointmentParticipantInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        return AppointmentParticipantsTable::configure($table);
    }
}
