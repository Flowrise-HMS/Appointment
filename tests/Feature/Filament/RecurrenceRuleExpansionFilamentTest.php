<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\AppointmentRecurrenceRuleResource;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages\CreateAppointmentRecurrenceRule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages\EditAppointmentRecurrenceRule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentRecurrenceRules\Pages\ListAppointmentRecurrenceRules;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\EditAppointment;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\RelationManagers\RecurrenceRulesRelationManager;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentRecurrenceRule;
use Modules\Appointment\Settings\AppointmentSettings;
use Modules\Core\Models\Branch;
use Tests\TestCase;

/**
 * Recurrence rules entered in the panel book their occurrences.
 */
uses(TestCase::class, DatabaseTransactions::class);

beforeEach(function (): void {
    $this->requireModule('Appointment');
    $this->migrateModules(['Core', 'Patient', 'Staff', 'Appointment']);
    $this->branch = Branch::factory()->create();
    $this->setCurrentBranch($this->branch);
    $start = now()->addWeek()->startOfDay()->setTime(9, 0);
    $this->appointment = Appointment::factory()->create([
        'branch_id' => $this->branch->id,
        'practitioner_primary_id' => null,
        'status' => AppointmentStatus::BOOKED,
        'start_at' => $start,
        'end_at' => $start->copy()->addMinutes(30),
    ]);
});

function generatedCount(AppointmentRecurrenceRule $rule): int
{
    return $rule->generatedAppointments()->count();
}

it('books occurrences when a rule is created on the resource page', function (): void {
    $this->actingAsResourceUser('AppointmentRecurrenceRule', ['viewAny', 'create', 'update'], ['branch_id' => $this->branch->id]);

    Livewire::test(CreateAppointmentRecurrenceRule::class)
        ->fillForm([
            'appointment_id' => $this->appointment->id,
            'frequency' => 'weekly',
            'interval' => 1,
            'occurrence_count' => 3,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified('Recurring appointments');

    $rule = AppointmentRecurrenceRule::query()->where('appointment_id', $this->appointment->id)->sole();

    expect(generatedCount($rule))->toBe(3)
        ->and($rule->branch_id)->toBe($this->branch->id);
});

it('books the extra occurrences when a rule is extended on edit', function (): void {
    $this->actingAsResourceUser('AppointmentRecurrenceRule', ['viewAny', 'view', 'update'], ['branch_id' => $this->branch->id]);
    $rule = AppointmentRecurrenceRule::create([
        'appointment_id' => $this->appointment->id,
        'frequency' => 'weekly',
        'interval' => 1,
        'occurrence_count' => 2,
    ]);
    app(Modules\Appointment\Classes\Services\RecurrenceExpansionService::class)->expand($rule);

    Livewire::test(EditAppointmentRecurrenceRule::class, ['record' => $rule->getRouteKey()])
        ->fillForm(['occurrence_count' => 4])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(generatedCount($rule))->toBe(4);
});

it('regenerates idempotently and cancels the series from the table', function (): void {
    $this->actingAsResourceUser('AppointmentRecurrenceRule', ['viewAny', 'view', 'update'], ['branch_id' => $this->branch->id]);
    $rule = AppointmentRecurrenceRule::create([
        'appointment_id' => $this->appointment->id,
        'frequency' => 'daily',
        'interval' => 1,
        'occurrence_count' => 3,
    ]);

    $page = Livewire::test(ListAppointmentRecurrenceRules::class);
    $page->callAction(TestAction::make('generateOccurrences')->table($rule));
    $page->callAction(TestAction::make('generateOccurrences')->table($rule));

    expect(generatedCount($rule))->toBe(3);

    $page->callAction(TestAction::make('cancelSeries')->table($rule))
        ->assertNotified('Series cancelled');

    expect($rule->refresh()->is_active)->toBeFalse()
        ->and($rule->generatedAppointments()->where('status', AppointmentStatus::CANCELLED)->count())->toBe(3);
});

it('books occurrences when a rule is added from the appointment relation manager', function (): void {
    $this->actingAsResourceUser('AppointmentRecurrenceRule', ['viewAny', 'view', 'create', 'update'], ['branch_id' => $this->branch->id]);

    Livewire::test(RecurrenceRulesRelationManager::class, [
        'ownerRecord' => $this->appointment,
        'pageClass' => EditAppointment::class,
    ])
        ->callAction(TestAction::make('create')->table(), [
            'frequency' => 'weekly',
            'interval' => 2,
            'occurrence_count' => 2,
        ])
        ->assertHasNoFormErrors();

    $rule = AppointmentRecurrenceRule::query()->where('appointment_id', $this->appointment->id)->sole();

    expect(generatedCount($rule))->toBe(2);
});

it('blocks the recurrence resource when recurrence is disabled in settings', function (): void {
    $this->actingAsResourceUser('AppointmentRecurrenceRule', ['viewAny'], ['branch_id' => $this->branch->id]);

    AppointmentSettings::fake(['recurrence_enabled' => false]);
    expect(AppointmentRecurrenceRuleResource::canAccess())->toBeFalse();

    AppointmentSettings::fake(['recurrence_enabled' => true]);
    expect(AppointmentRecurrenceRuleResource::canAccess())->toBeTrue();
});
