<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Enums\WaitlistEntryStatus;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Pages\CreateWaitlistEntry;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Pages\EditWaitlistEntry;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Pages\ListWaitlistEntries;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\Pages\ViewWaitlistEntry;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\WaitlistEntryResource;
use Modules\Appointment\Models\WaitlistEntry;
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
    'resource' => WaitlistEntryResource::class,
    'subject' => 'WaitlistEntry',
    'model' => WaitlistEntry::class,
    'listPage' => ListWaitlistEntries::class,
    'createPage' => CreateWaitlistEntry::class,
    'editPage' => EditWaitlistEntry::class,
    'viewPage' => ViewWaitlistEntry::class,
    'sortColumn' => 'urgency_score',
    'filter' => [
        'name' => 'status',
        'value' => WaitlistEntryStatus::WAITING->value,
        'attribute' => 'status',
    ],
    'hasBulkDelete' => true,
    'hasRecordDelete' => true,
    'userAttributes' => fn (TestCase $test): array => ['branch_id' => $test->branch->id],
    'makeRecord' => fn (TestCase $test, array $attributes = []): WaitlistEntry => WaitlistEntry::factory()->create([
        'branch_id' => $test->branch->id,
        'patient_id' => $test->patient->id,
        'status' => WaitlistEntryStatus::WAITING,
        ...$attributes,
    ]),
    'makeRecords' => function (TestCase $test, int $count) {
        return collect(range(1, $count))->map(fn (int $offset): WaitlistEntry => WaitlistEntry::factory()->create([
            'branch_id' => $test->branch->id,
            'status' => WaitlistEntryStatus::WAITING,
            'urgency_score' => $offset * 10,
        ]));
    },
    'createForm' => fn (TestCase $test): array => [
        'branch_id' => $test->branch->id,
        'patient_id' => $test->patient->id,
        'urgency_score' => 10,
        'wait_time_score' => 5,
        'referral_score' => 2,
        'manual_override_score' => 0,
        'computed_priority_score' => 17,
        'status' => WaitlistEntryStatus::WAITING->value,
    ],
    'updateForm' => fn (): array => [
        'urgency_score' => 20,
        'computed_priority_score' => 27,
    ],
    'schemaState' => fn (mixed $test, WaitlistEntry $record): array => [
        'urgency_score' => $record->urgency_score,
        'status' => $record->status,
    ],
    'requiredValidation' => [
        'branch is required' => [['branch_id' => null], ['branch_id' => 'required']],
        'status is required' => [['status' => null], ['status' => 'required']],
    ],
    'databaseHasOnCreate' => fn (mixed $test, array $payload): array => [
        'branch_id' => $payload['branch_id'],
        'patient_id' => $payload['patient_id'],
        'status' => $payload['status'],
    ],
]);
