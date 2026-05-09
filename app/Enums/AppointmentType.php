<?php

namespace Modules\Appointment\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AppointmentType: string implements HasColor, HasDescription, HasLabel
{
    case OUTPATIENT = 'outpatient';
    case INPATIENT = 'inpatient';
    case EMERGENCY = 'emergency';
    case VIRTUAL = 'virtual';
    case FOLLOW_UP = 'follow_up';
    case PROCEDURE = 'procedure';
    case SCREENING = 'screening';
    case CONSULT = 'consult';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::OUTPATIENT => 'Outpatient',
            self::INPATIENT => 'Inpatient',
            self::EMERGENCY => 'Emergency',
            self::VIRTUAL => 'Virtual / telemedicine',
            self::FOLLOW_UP => 'Follow-up',
            self::PROCEDURE => 'Procedure',
            self::SCREENING => 'Screening',
            self::CONSULT => 'Consultation',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::OUTPATIENT => 'Routine visit without overnight stay.',
            self::INPATIENT => 'Care involving admission or bed assignment.',
            self::EMERGENCY => 'Urgent or unscheduled acute presentation.',
            self::VIRTUAL => 'Remote visit using telehealth.',
            self::FOLLOW_UP => 'Continuity visit after prior care.',
            self::PROCEDURE => 'Interventional or surgical service slot.',
            self::SCREENING => 'Preventive or population health screening.',
            self::CONSULT => 'Specialist or second-opinion consultation.',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::OUTPATIENT => 'info',
            self::INPATIENT => 'primary',
            self::EMERGENCY => 'danger',
            self::VIRTUAL => 'warning',
            self::FOLLOW_UP => 'gray',
            self::PROCEDURE => 'secondary',
            self::SCREENING => 'success',
            self::CONSULT => 'gray',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function default(): self
    {
        return self::OUTPATIENT;
    }
}
