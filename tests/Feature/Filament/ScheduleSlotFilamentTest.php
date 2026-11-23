<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Appointment\Enums\SlotStatus;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Schemas\AppointmentForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages\CreateAppointmentSchedule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages\EditAppointmentSchedule;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\AppointmentSchedules\Pages\ListAppointmentSchedules;
use Modules\Appointment\Models\AppointmentSchedule;
use Modules\Core\Models\Branch;
use Tests\TestCase;

uses(TestCase::class, DatabaseTransactions::class);

beforeEach(function (): void {
    $this->requireModule('Appointment');
    $this->migrateModules(['Core', 'Patient', 'Staff', 'Appointment']);
    Carbon::setTestNow(Carbon::parse('next monday 07:00', 'UTC'));
    $this->branch = Branch::factory()->create(['timezone' => 'UTC']);
    $this->setCurrentBranch($this->branch);
});

afterEach(fn () => Carbon::setTestNow());

it('generates slots when a schedule with hours is created', function (): void {
    $this->actingAsResourceUser('AppointmentSchedule', ['viewAny', 'create', 'update'], ['branch_id' => $this->branch->id]);

    Livewire::test(CreateAppointmentSchedule::class)
        ->fillForm([
            'name' => 'Monday clinic',
            'branch_id' => $this->branch->id,
            'slot_minutes' => 30,
            'generation_days' => 7,
            'timezone' => 'UTC',
            'availabilities' => [
                ['weekday' => 1, 'start_time' => '09:00', 'end_time' => '10:00'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified('Slots generated');

    $schedule = AppointmentSchedule::query()->where('name', 'Monday clinic')->sole();

    expect($schedule->slots()->count())->toBe(2);
});

it('regenerates slots when hours change and from the table action', function (): void {
    $this->actingAsResourceUser('AppointmentSchedule', ['viewAny', 'view', 'update'], ['branch_id' => $this->branch->id]);
    $schedule = AppointmentSchedule::factory()->create([
        'branch_id' => $this->branch->id,
        'slot_minutes' => 30,
        'generation_days' => 7,
        'timezone' => 'UTC',
    ]);
    $schedule->availabilities()->create(['weekday' => 1, 'start_time' => '09:00', 'end_time' => '10:00']);

    Livewire::test(ListAppointmentSchedules::class)
        ->callAction(TestAction::make('generateSlots')->table($schedule))
        ->assertNotified('Slots generated');
    expect($schedule->slots()->count())->toBe(2);

    Livewire::test(EditAppointmentSchedule::class, ['record' => $schedule->getRouteKey()])
        ->fillForm(['slot_minutes' => 15])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($schedule->slots()->count())->toBe(4);
});

it('offers a practitioner\'s free slots on the appointment form', function (): void {
    $practitionerId = (string) Str::uuid();
    $schedule = AppointmentSchedule::factory()->create([
        'branch_id' => $this->branch->id,
        'practitioner_id' => $practitionerId,
        'slot_minutes' => 30,
        'generation_days' => 1,
        'timezone' => 'UTC',
    ]);
    $schedule->availabilities()->create(['weekday' => 1, 'start_time' => '09:00', 'end_time' => '10:00']);
    app(Modules\Appointment\Classes\Services\SlotGenerationService::class)->generate($schedule);
    $schedule->slots()->orderBy('start_at')->first()->forceFill(['status' => SlotStatus::BUSY])->save();

    $options = AppointmentForm::freeSlotOptions($practitionerId, $this->branch->id);

    expect($options)->toHaveCount(1)
        ->and(array_values($options)[0])->toContain('09:30')
        ->and(AppointmentForm::freeSlotOptions(null, $this->branch->id))->toBe([])
        ->and(AppointmentForm::freeSlotOptions((string) Str::uuid(), $this->branch->id))->toBe([]);
});
