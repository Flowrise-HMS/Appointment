<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Enums\SyncOutboxStatus;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\AppointmentSyncOutboxResource;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages\EditAppointmentSyncOutbox;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages\ListAppointmentSyncOutboxes;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSyncOutboxes\Pages\ViewAppointmentSyncOutbox;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentSyncOutbox;
use Modules\Core\Models\Branch;
use Tests\Support\FilamentResourceTestSuite;
use Tests\TestCase;

uses(TestCase::class, DatabaseTransactions::class);

beforeEach(function (): void {
    $this->requireModule('Appointment');
    $this->migrateModules(['Core', 'Patient', 'Staff', 'Appointment']);
    $this->branch = Branch::factory()->create();
    $this->setCurrentBranch($this->branch);
    $this->appointment = Appointment::factory()->create(['branch_id' => $this->branch->id]);
});

FilamentResourceTestSuite::register([
    'resource' => AppointmentSyncOutboxResource::class,
    'subject' => 'AppointmentSyncOutbox',
    'model' => AppointmentSyncOutbox::class,
    'listPage' => ListAppointmentSyncOutboxes::class,
    'editPage' => EditAppointmentSyncOutbox::class,
    'viewPage' => ViewAppointmentSyncOutbox::class,
    'searchColumn' => 'event_name',
    'sortColumn' => 'event_name',
    'filter' => [
        'name' => 'status',
        'value' => SyncOutboxStatus::PENDING->value,
        'attribute' => 'status',
    ],
    'hasBulkDelete' => true,
    'hasRecordDelete' => true,
    'userAttributes' => fn (TestCase $test): array => ['branch_id' => $test->branch->id],
    'makeRecord' => fn (TestCase $test, array $attributes = []): AppointmentSyncOutbox => AppointmentSyncOutbox::factory()->create([
        'branch_id' => $test->branch->id,
        'aggregate_id' => $test->appointment->id,
        'status' => SyncOutboxStatus::PENDING,
        'payload' => ['event' => 'appointment.created'],
        ...$attributes,
    ]),
    'makeRecords' => function (TestCase $test, int $count) {
        $events = ['appointment.created', 'appointment.updated', 'appointment.cancelled', 'appointment.rescheduled'];
        $records = collect();

        for ($index = 0; $index < $count; $index++) {
            $records->push(AppointmentSyncOutbox::factory()->create([
                'branch_id' => $test->branch->id,
                'aggregate_id' => $test->appointment->id,
                'status' => SyncOutboxStatus::PENDING,
                'event_name' => $events[$index % count($events)].'-'.$index,
                'payload' => ['event' => 'appointment.created'],
            ]));
        }

        return $records;
    },
    'updateForm' => fn (): array => [
        'event_name' => 'appointment.updated',
        'status' => SyncOutboxStatus::PENDING->value,
    ],
    'schemaState' => fn (mixed $test, AppointmentSyncOutbox $record): array => [
        'event_name' => $record->event_name,
        'aggregate_type' => $record->aggregate_type,
    ],
]);
