<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\AppointmentRecurrenceRuleResource;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages\CreateAppointmentRecurrenceRule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages\EditAppointmentRecurrenceRule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages\ListAppointmentRecurrenceRules;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages\ViewAppointmentRecurrenceRule;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentRecurrenceRule;
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
    'resource' => AppointmentRecurrenceRuleResource::class,
    'subject' => 'AppointmentRecurrenceRule',
    'model' => AppointmentRecurrenceRule::class,
    'listPage' => ListAppointmentRecurrenceRules::class,
    'createPage' => CreateAppointmentRecurrenceRule::class,
    'editPage' => EditAppointmentRecurrenceRule::class,
    'viewPage' => ViewAppointmentRecurrenceRule::class,
    'hasBulkDelete' => true,
    'hasRecordDelete' => true,
    'userAttributes' => fn (TestCase $test): array => ['branch_id' => $test->branch->id],
    'makeRecord' => fn (TestCase $test, array $attributes = []): AppointmentRecurrenceRule => AppointmentRecurrenceRule::factory()->create([
        'appointment_id' => $test->appointment->id,
        'branch_id' => $test->branch->id,
        ...$attributes,
    ]),
    'makeRecords' => fn (TestCase $test, int $count) => AppointmentRecurrenceRule::factory()->count($count)->create([
        'appointment_id' => $test->appointment->id,
        'branch_id' => $test->branch->id,
    ]),
]);
