<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\AppointmentResource;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\CreateAppointment;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\EditAppointment;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\ListAppointments;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\ViewAppointment;
use Modules\Appointment\Models\Appointment;
use Modules\Core\Models\Branch;
use Modules\Patient\Models\Patient;
use Tests\Support\FilamentResourceTestSuite;
use Tests\TestCase;

uses(TestCase::class, DatabaseTransactions::class);

beforeEach(function (): void {
    $this->requireModule('Appointment');
    $this->migrateModules(['Core', 'Patient', 'Staff', 'Appointment']);
    $this->branch = Branch::factory()->create();
    $this->setCurrentBranch($this->branch);
    $this->patient = Patient::factory()->create(['branch_id' => $this->branch->id]);
});

FilamentResourceTestSuite::register([
    'resource' => AppointmentResource::class,
    'subject' => 'Appointment',
    'model' => Appointment::class,
    'listPage' => ListAppointments::class,
    'createPage' => CreateAppointment::class,
    'editPage' => EditAppointment::class,
    'viewPage' => ViewAppointment::class,
    'filter' => [
        'name' => 'status',
        'value' => AppointmentStatus::BOOKED->value,
        'attribute' => 'status',
    ],
    'hasBulkDelete' => true,
    'hasRecordDelete' => true,
    'softDeletes' => true,
    'userAttributes' => fn (TestCase $test): array => ['branch_id' => $test->branch->id],
    'makeRecord' => fn (TestCase $test, array $attributes = []): Appointment => Appointment::factory()->create([
        'branch_id' => $test->branch->id,
        'patient_id' => $test->patient->id,
        'status' => AppointmentStatus::BOOKED,
        ...$attributes,
    ]),
    'makeRecords' => fn (TestCase $test, int $count) => Appointment::factory()->count($count)->create([
        'branch_id' => $test->branch->id,
        'patient_id' => $test->patient->id,
        'status' => AppointmentStatus::BOOKED,
    ]),
    'createForm' => fn (TestCase $test): array => [
        'patient_id' => $test->patient->id,
        'branch_id' => $test->branch->id,
        'status' => AppointmentStatus::BOOKED->value,
        'priority' => 5,
        'start_at' => now()->addDay()->toDateTimeString(),
        'end_at' => now()->addDay()->addHour()->toDateTimeString(),
        'reason_text' => 'Routine review',
    ],
    'updateForm' => fn (): array => [
        'reason_text' => 'Updated appointment reason',
        'priority' => 4,
    ],
    'schemaState' => fn (mixed $test, Appointment $record): array => [
        'status' => $record->status,
        'reason_text' => $record->reason_text,
    ],
    'requiredValidation' => [
        'patient is required' => [['patient_id' => null], ['patient_id' => 'required']],
    ],
    'databaseHasOnCreate' => fn (mixed $test, array $payload): array => [
        'patient_id' => $payload['patient_id'],
        'status' => $payload['status'],
        'reason_text' => $payload['reason_text'],
    ],
]);
