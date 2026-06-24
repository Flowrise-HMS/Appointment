<?php

namespace Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\AppointmentType;
use Modules\Core\Classes\Services\BranchService;
use Modules\Core\Enums\CoverageType;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Department;
use Modules\Core\Models\Location;
use Modules\Core\Models\Service;
use Modules\Patient\Models\Patient;

class AppointmentForm
{
    /**
     * Full resource form (create/edit).
     *
     * @param  ?string  $defaultBranchId  Default branch when patient context is known.
     */
    public static function configure(
        Schema $schema,
        bool $hidePatient = false,
        ?string $defaultBranchId = null,
        bool $includeExtendedContext = true,
    ): Schema {
        return $schema
            ->columns(3)
            ->components(array_merge(
                [self::patientAndServiceSection($hidePatient, $defaultBranchId)],
                self::baseScheduleSections(),
                $includeExtendedContext ? self::extendedContextSection() : [],
            ));
    }

    /**
     * Patient + branch + location (patient select hidden when scheduling from a patient context page).
     */
    public static function patientAndServiceSection(bool $hidePatient = false, ?string $defaultBranchId = null): Section
    {
        if (! $defaultBranchId) {
            $defaultBranchId = app(BranchService::class)->getDefaultBranchId();
        }

        return Section::make('Patient and Service Context')
            ->columnSpan(2)
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('patient_id')
                            ->label('Patient')
                            ->options(fn () => Patient::query()?->orderBy('first_name')?->pluck('mrn', 'id')?->toArray())
                            ->searchable()
                            ->required(! $hidePatient)
                            ->hidden($hidePatient)
                            ->dehydrated()
                            ->helperText('Select the patient this appointment is scheduled for.'),
                        Select::make('branch_id')
                            ->label('Branch')
                            ->options(fn () => Branch::query()?->orderBy('name')->pluck('name', 'id')?->toArray())
                            ->searchable()
                            ->required()
                            ->default($defaultBranchId),
                    ]),
                Grid::make(3)
                    ->schema([
                        Select::make('location_id')
                            ->label('Location')
                            ->options(fn () => Location::query()?->orderBy('name')?->pluck('name', 'id')?->toArray())
                            ->searchable(),
                        Select::make('department_id')
                            ->label('Department')
                            ->options(fn () => Department::query()?->orderBy('name')?->pluck('name', 'id')?->toArray())
                            ->searchable(),
                        TextInput::make('practitioner_primary_id')
                            ->label('Practitioner Reference')
                            ->maxLength(36)
                            ->helperText('Use practitioner UUID/reference until Staff directory binding is added.'),
                    ]),
                Grid::make(2)
                    ->schema([
                        Select::make('service_id')
                            ->label('Service (for billing)')
                            ->options(fn () => Service::nonMedication()?->orderBy('name')?->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->helperText('Select the billable service for this appointment. Required for self-pay check-in billing.'),
                        Select::make('coverage_type')
                            ->label('Coverage')
                            ->options(CoverageType::class)
                            ->placeholder(__('Default (no coverage)'))
                            ->helperText('NHIS, private insurance, or self-pay. Affects check-in billing.'),
                    ]),
            ]);
    }

    /**
     * Status, priority, type, time window, and a short reason (used for workspace slideOver).
     *
     * @return array<int, Section>
     */
    public static function baseScheduleSections(): array
    {
        return [
            Section::make('Schedule and Status')
                ->columnSpan(1)
                ->schema([
                    Select::make('status')
                        ->options(AppointmentStatus::class)
                        ->default(AppointmentStatus::BOOKED)
                        ->required(),
                    TextInput::make('priority')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(99)
                        ->default(5)
                        ->required(),
                    Select::make('appointment_type')
                        ->options(AppointmentType::class)
                        ->default(AppointmentType::OUTPATIENT)
                        ->searchable()
                        ->nullable(),
                ]),
            Section::make('Time Window')
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('start_at')
                                ->seconds(false)
                                ->default(now())
                                ->required()
                                ->native(false),
                            DateTimePicker::make('end_at')
                                ->seconds(false)
                                ->required()
                                ->native(false)
                                ->after('start_at')
                                ->helperText('End time must be greater than start time.'),
                        ]),
                ]),
            Section::make('Reason')
                ->columnSpanFull()
                ->schema([
                    Textarea::make('reason_text')
                        ->rows(2)
                        ->maxLength(2000),
                ]),
        ];
    }

    /**
     * @return array<int, Section>
     */
    public static function extendedContextSection(): array
    {
        return [
            Section::make('Clinical Context')
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('service_category_code')->maxLength(50),
                            TextInput::make('service_type_code')->maxLength(50),
                            TextInput::make('reason_code')->maxLength(50),
                            TextInput::make('cancellation_reason_code')->maxLength(50),
                        ]),
                    Textarea::make('notes_encrypted')
                        ->label('Protected Notes')
                        ->rows(3)
                        ->helperText('Sensitive content is encrypted at rest.'),
                ]),
        ];
    }

    /**
     * @deprecated Use {@see configure()} with flags or {@see baseScheduleSections()}.
     *
     * @return array<int, Section>
     */
    public static function quickElements(): array
    {
        return array_merge(self::baseScheduleSections(), self::extendedContextSection());
    }

    /**
     * Minimal schema for Clinical Workspace quick-create (slideover): required patient, branch,
     * schedule fields, and a short required reason. No extended FHIR / notes / recurrence.
     */
    public static function workspaceQuickCreate(Schema $schema, ?string $defaultBranchId = null): Schema
    {
        $base = self::baseScheduleSections();
        $reasonSection = Section::make('Reason')
            ->columnSpanFull()
            ->schema([
                Textarea::make('reason_text')
                    ->rows(2)
                    ->maxLength(2000)
                    ->required(),
            ]);

        return $schema
            ->columns(1)
            ->components([
                self::patientAndServiceSection(hidePatient: false, defaultBranchId: $defaultBranchId),
                $base[0],
                $base[1],
                // Replace optional reason from baseScheduleSections with required reason for workspace.
                $reasonSection,
            ]);
    }
}
