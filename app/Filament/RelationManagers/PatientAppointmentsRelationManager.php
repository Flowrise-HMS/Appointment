<?php

namespace Modules\Appointment\Filament\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Schemas\AppointmentForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Tables\AppointmentsTable;

class PatientAppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Appointments');
    }

    public function form(Schema $schema): Schema
    {
        return AppointmentForm::configure($schema, hidePatient: true);
    }

    public function table(Table $table): Table
    {
        return AppointmentsTable::configure($table);
    }
}
