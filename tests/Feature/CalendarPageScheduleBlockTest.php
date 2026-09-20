<?php

namespace Modules\Appointment\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Appointment\Filament\Clusters\Appointment\Pages\Calendar;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\ListAppointments;
use Modules\Appointment\Filament\Widgets\AppointmentsCalendar;
use Modules\Appointment\Filament\Widgets\ScheduleBlocksCalendar;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\ScheduleBlock;
use Modules\Core\Models\Branch;
use Modules\Patient\Models\Patient;
use Modules\Staff\Models\Staff;
use Tests\TestCase;

class CalendarPageScheduleBlockTest extends TestCase
{
    use DatabaseTransactions;

    private Branch $branch;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateModules(['Core', 'Patient', 'Staff', 'Appointment']);
        Gate::before(fn () => true);

        $this->branch = Branch::factory()->default()->create();
        $this->user = User::factory()->create(['branch_id' => $this->branch->id]);
        Livewire::actingAs($this->user);
    }

    public function test_appointments_list_links_to_the_calendar_page(): void
    {
        Livewire::test(ListAppointments::class)
            ->assertActionExists('calendar')
            ->assertActionHasUrl('calendar', Calendar::getUrl());
    }

    public function test_calendar_page_creates_a_schedule_block_for_any_user(): void
    {
        $practitioner = Staff::factory()->create(['branch_id' => $this->branch->id]);

        Livewire::test(Calendar::class)
            ->assertActionExists('createScheduleBlock')
            ->callAction('createScheduleBlock', data: [
                'branch_id' => $this->branch->id,
                'practitioner_id' => $practitioner->id,
                'reason' => 'Theatre list',
                'blocked_from' => now()->addDay()->setTime(8, 0),
                'blocked_to' => now()->addDay()->setTime(12, 0),
            ])
            ->assertHasNoActionErrors()
            ->assertDispatched('calendar--refresh');

        $this->assertDatabaseHas('appointment_schedule_blocks', [
            'practitioner_id' => $practitioner->id,
            'reason' => 'Theatre list',
        ]);
    }

    public function test_schedule_block_widget_shows_branch_blocks_for_users_without_a_staff_profile(): void
    {
        $practitioner = Staff::factory()->create(['branch_id' => $this->branch->id]);
        ScheduleBlock::factory()->create([
            'branch_id' => $this->branch->id,
            'practitioner_id' => $practitioner->id,
            'reason' => 'Ward round',
            'blocked_from' => now()->setTime(9, 0),
            'blocked_to' => now()->setTime(10, 0),
        ]);

        $component = Livewire::test(ScheduleBlocksCalendar::class)->assertOk();

        $events = $component->instance()->getEventsJs([
            'startStr' => now()->startOfDay()->toIso8601String(),
            'endStr' => now()->endOfDay()->toIso8601String(),
            'tzOffset' => 0,
        ]);

        $this->assertCount(1, $events);
        $this->assertStringContainsString('Ward round', (string) $events[0]['title']);
    }

    public function test_appointments_calendar_serialises_appointments_as_events(): void
    {
        $patient = Patient::withoutEvents(fn () => Patient::factory()->create(['branch_id' => $this->branch->id, 'mrn' => 'FR-CAL-00001']));
        Appointment::factory()->create([
            'branch_id' => $this->branch->id,
            'patient_id' => $patient->id,
            'start_at' => now()->setTime(9, 0),
            'end_at' => now()->setTime(9, 30),
        ]);

        $events = Livewire::test(AppointmentsCalendar::class)
            ->assertOk()
            ->instance()
            ->getEventsJs([
                'startStr' => now()->startOfDay()->toIso8601String(),
                'endStr' => now()->endOfDay()->toIso8601String(),
                'tzOffset' => 0,
            ]);

        $this->assertCount(1, $events);
        $this->assertStringContainsString('FR-CAL-00001', (string) $events[0]['title']);
    }
}
