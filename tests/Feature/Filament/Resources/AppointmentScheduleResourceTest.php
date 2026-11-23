<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\AppointmentScheduleResource;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages\CreateAppointmentSchedule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages\EditAppointmentSchedule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages\ListAppointmentSchedules;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages\ViewAppointmentSchedule;
use Modules\Appointment\Models\AppointmentSchedule;
use Modules\Core\Models\Branch;
use Tests\Support\FilamentResourceTestSuite;
use Tests\TestCase;

uses(TestCase::class, DatabaseTransactions::class);

beforeEach(function (): void {
    $this->requireModule('Appointment');
    $this->migrateModules(['Core', 'Patient', 'Staff', 'Appointment']);
    $this->branch = Branch::factory()->create();
    $this->setCurrentBranch($this->branch);
});

FilamentResourceTestSuite::register([
    'resource' => AppointmentScheduleResource::class,
    'subject' => 'AppointmentSchedule',
    'model' => AppointmentSchedule::class,
    'listPage' => ListAppointmentSchedules::class,
    'createPage' => CreateAppointmentSchedule::class,
    'editPage' => EditAppointmentSchedule::class,
    'viewPage' => ViewAppointmentSchedule::class,
    'searchColumn' => 'name',
    'createForm' => fn (TestCase $test): array => [
        'name' => 'Morning clinic',
        'branch_id' => $test->branch->id,
        'slot_minutes' => 20,
        'generation_days' => 7,
    ],
    'updateForm' => fn (TestCase $test, AppointmentSchedule $record): array => [
        'name' => 'Afternoon clinic',
    ],
    'requiredValidation' => [
        [['name' => null], ['name' => 'required']],
        [['slot_minutes' => 2], ['slot_minutes' => 'min']],
    ],
    'hasBulkDelete' => true,
    'hasRecordDelete' => true,
    'userAttributes' => fn (TestCase $test): array => ['branch_id' => $test->branch->id],
    'makeRecord' => fn (TestCase $test, array $attributes = []): AppointmentSchedule => AppointmentSchedule::factory()->create([
        'branch_id' => $test->branch->id,
        ...$attributes,
    ]),
    'makeRecords' => fn (TestCase $test, int $count) => AppointmentSchedule::factory()->count($count)->create([
        'branch_id' => $test->branch->id,
    ]),
]);
