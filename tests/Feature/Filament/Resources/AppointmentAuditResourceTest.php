<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\AppointmentAuditResource;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages\CreateAppointmentAudit;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages\EditAppointmentAudit;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages\ListAppointmentAudits;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentAudits\Pages\ViewAppointmentAudit;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentAudit;
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
    'resource' => AppointmentAuditResource::class,
    'subject' => 'AppointmentAudit',
    'model' => AppointmentAudit::class,
    'listPage' => ListAppointmentAudits::class,
    'createPage' => CreateAppointmentAudit::class,
    'editPage' => EditAppointmentAudit::class,
    'viewPage' => ViewAppointmentAudit::class,
    'hasBulkDelete' => true,
    'hasRecordDelete' => true,
    'userAttributes' => fn (TestCase $test): array => ['branch_id' => $test->branch->id],
    'makeRecord' => fn (TestCase $test, array $attributes = []): AppointmentAudit => AppointmentAudit::factory()->create([
        'appointment_id' => $test->appointment->id,
        'branch_id' => $test->branch->id,
        ...$attributes,
    ]),
    'makeRecords' => fn (TestCase $test, int $count) => AppointmentAudit::factory()->count($count)->create([
        'appointment_id' => $test->appointment->id,
        'branch_id' => $test->branch->id,
    ]),
]);
