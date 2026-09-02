<?php

namespace Modules\Appointment\Filament\Exports;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Modules\Appointment\Models\Appointment;

class AppointmentExporter extends Exporter
{
    protected static ?string $model = Appointment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('patient.mrn'),
            ExportColumn::make('patient.first_name'),
            ExportColumn::make('patient.last_name'),
            ExportColumn::make('primaryPractitioner.first_name'),
            ExportColumn::make('primaryPractitioner.last_name'),
            ExportColumn::make('status'),
            ExportColumn::make('appointment_type'),
            ExportColumn::make('priority'),
            ExportColumn::make('coverage_type'),
            ExportColumn::make('service.name'),
            ExportColumn::make('department.name'),
            ExportColumn::make('location.name'),
            ExportColumn::make('reason_text'),
            ExportColumn::make('start_at'),
            ExportColumn::make('end_at'),
            ExportColumn::make('checked_in_at'),
            ExportColumn::make('completed_at'),
            ExportColumn::make('branch.name'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your appointment export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
