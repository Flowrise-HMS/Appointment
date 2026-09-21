<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages\CreateScheduleBlock;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages\EditScheduleBlock;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages\ListScheduleBlocks;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\Pages\ViewScheduleBlock;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\ScheduleBlocks\ScheduleBlockResource;
use Modules\Appointment\Models\ScheduleBlock;
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
    'resource' => ScheduleBlockResource::class,
    'subject' => 'ScheduleBlock',
    'model' => ScheduleBlock::class,
    'listPage' => ListScheduleBlocks::class,
    'createPage' => CreateScheduleBlock::class,
    'editPage' => EditScheduleBlock::class,
    'viewPage' => ViewScheduleBlock::class,
    'searchColumn' => 'resource_reference',
    'sortColumn' => 'resource_reference',
    'hasBulkDelete' => true,
    'hasRecordDelete' => true,
    'userAttributes' => fn (TestCase $test): array => ['branch_id' => $test->branch->id],
    'makeRecord' => fn (TestCase $test, array $attributes = []): ScheduleBlock => ScheduleBlock::factory()->create([
        'branch_id' => $test->branch->id,
        'resource_reference' => 'theatre-'.fake()->unique()->numerify('##'),
        ...$attributes,
    ]),
    'makeRecords' => fn (TestCase $test, int $count) => ScheduleBlock::factory()->count($count)->sequence(
        fn ($sequence) => [
            'branch_id' => $test->branch->id,
            'resource_reference' => 'block-'.$sequence->index,
        ],
    )->create(),
    'createForm' => fn (TestCase $test): array => [
        'branch_id' => $test->branch->id,
        'resource_reference' => 'or-1',
        'reason' => 'Theatre maintenance',
        'blocked_from' => now()->addDay()->toDateTimeString(),
        'blocked_to' => now()->addDay()->addHours(4)->toDateTimeString(),
    ],
    'updateForm' => fn (): array => [
        'reason' => 'Updated block reason',
        'resource_reference' => 'or-2',
    ],
    'schemaState' => fn (mixed $test, ScheduleBlock $record): array => [
        'reason' => $record->reason,
    ],
    'requiredValidation' => [
        'blocked from is required' => [['blocked_from' => null], ['blocked_from' => 'required']],
        'blocked to is required' => [['blocked_to' => null], ['blocked_to' => 'required']],
    ],
    'databaseHasOnCreate' => fn (mixed $test, array $payload): array => [
        'branch_id' => $payload['branch_id'],
        'reason' => $payload['reason'],
    ],
]);
