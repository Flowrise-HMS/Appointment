<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Enums\AppointmentParticipantStatus;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\AppointmentParticipantResource;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages\CreateAppointmentParticipant;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages\EditAppointmentParticipant;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages\ListAppointmentParticipants;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentParticipants\Pages\ViewAppointmentParticipant;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentParticipant;
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
    $this->appointment = Appointment::factory()->create([
        'branch_id' => $this->branch->id,
        'patient_id' => $this->patient->id,
    ]);
});

FilamentResourceTestSuite::register([
    'resource' => AppointmentParticipantResource::class,
    'subject' => 'AppointmentParticipant',
    'model' => AppointmentParticipant::class,
    'listPage' => ListAppointmentParticipants::class,
    'createPage' => CreateAppointmentParticipant::class,
    'editPage' => EditAppointmentParticipant::class,
    'viewPage' => ViewAppointmentParticipant::class,
    'searchColumn' => 'actor_reference',
    'sortColumn' => 'participant_type',
    'hasBulkDelete' => true,
    'hasRecordDelete' => true,
    'userAttributes' => fn (TestCase $test): array => ['branch_id' => $test->branch->id],
    'makeRecord' => fn (TestCase $test, array $attributes = []): AppointmentParticipant => AppointmentParticipant::factory()->create([
        'appointment_id' => $test->appointment->id,
        'branch_id' => $test->branch->id,
        'actor_reference' => (string) fake()->unique()->uuid(),
        ...$attributes,
    ]),
    'makeRecords' => function (TestCase $test, int $count) {
        $records = collect();

        for ($index = 0; $index < $count; $index++) {
            $records->push(AppointmentParticipant::factory()->create([
                'appointment_id' => $test->appointment->id,
                'branch_id' => $test->branch->id,
                'participant_type' => 'type-'.$index,
                'actor_reference' => 'actor-'.$index,
            ]));
        }

        return $records;
    },
    'createForm' => fn (TestCase $test): array => [
        'appointment_id' => $test->appointment->id,
        'participant_type' => 'practitioner',
        'actor_reference' => (string) fake()->uuid(),
        'status' => AppointmentParticipantStatus::ACCEPTED->value,
        'required' => true,
    ],
    'updateForm' => fn (): array => [
        'participant_type' => 'observer',
        'actor_reference' => (string) fake()->uuid(),
    ],
    'schemaState' => fn (mixed $test, AppointmentParticipant $record): array => [
        'participant_type' => $record->participant_type,
        'actor_reference' => $record->actor_reference,
    ],
    'requiredValidation' => [
        'participant type is required' => [['participant_type' => null], ['participant_type' => 'required']],
        'actor reference is required' => [['actor_reference' => null], ['actor_reference' => 'required']],
    ],
    'databaseHasOnCreate' => fn (mixed $test, array $payload): array => [
        'participant_type' => $payload['participant_type'],
        'actor_reference' => $payload['actor_reference'],
    ],
]);
