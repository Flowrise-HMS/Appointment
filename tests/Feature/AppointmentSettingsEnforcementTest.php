<?php

namespace Modules\Appointment\Tests\Feature;

use App\Models\User;
use Carbon\WeekDay;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Modules\Appointment\Classes\Services\AppointmentSchedulingService;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\CreateAppointment;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Pages\EditAppointment;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\RelationManagers\RecurrenceRulesRelationManager;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\Appointments\Schemas\AppointmentForm;
use Modules\Appointment\Filament\Clusters\Appointment\Resources\WaitlistEntries\WaitlistEntryResource;
use Modules\Appointment\Filament\Widgets\AppointmentsCalendar;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentSyncOutbox;
use Modules\Appointment\Settings\AppointmentSettings;
use Modules\Core\Models\Branch;
use Modules\Patient\Models\Patient;
use Tests\TestCase;

/**
 * The Appointment settings page used to store nine values nothing read.
 */
class AppointmentSettingsEnforcementTest extends TestCase
{
    use DatabaseTransactions;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateModules(['Core', 'Patient', 'Staff', 'Appointment']);
        Gate::before(fn () => true);
        $this->branch = Branch::factory()->default()->create();
        Livewire::actingAs(User::factory()->create(['branch_id' => $this->branch->id]));
    }

    public function test_form_defaults_come_from_the_settings(): void
    {
        AppointmentSettings::fake([
            'default_status' => AppointmentStatus::PENDING->value,
            'default_type' => 'inpatient',
            'default_duration_minutes' => 45,
            'telehealth_enabled' => false,
        ]);

        Livewire::test(CreateAppointment::class)
            ->assertSchemaStateSet([
                'status' => AppointmentStatus::PENDING,
                'appointment_type' => 'inpatient',
            ]);

        $this->assertArrayNotHasKey('virtual', AppointmentForm::appointmentTypeOptions());

        AppointmentSettings::fake(['telehealth_enabled' => true]);
        $this->assertArrayHasKey('virtual', AppointmentForm::appointmentTypeOptions());
    }

    public function test_calendar_first_day_follows_the_setting(): void
    {
        AppointmentSettings::fake(['calendar_first_day_of_week' => 0]);
        $this->assertSame(WeekDay::Sunday, (new AppointmentsCalendar)->getFirstDay());

        AppointmentSettings::fake(['calendar_first_day_of_week' => 1]);
        $this->assertSame(WeekDay::Monday, (new AppointmentsCalendar)->getFirstDay());
    }

    public function test_sync_outbox_is_skipped_when_external_sync_is_disabled(): void
    {
        AppointmentSettings::fake(['external_sync_enabled' => false]);
        $patient = Patient::withoutEvents(fn () => Patient::factory()->create(['branch_id' => $this->branch->id]));

        $appointment = app(AppointmentSchedulingService::class)->schedule([
            'branch_id' => $this->branch->id,
            'patient_id' => $patient->id,
            'status' => AppointmentStatus::BOOKED,
            'start_at' => now()->addDay()->setTime(9, 0),
            'end_at' => now()->addDay()->setTime(9, 30),
        ]);

        $this->assertSame(0, AppointmentSyncOutbox::query()->where('aggregate_id', $appointment->id)->count());
    }

    public function test_waitlist_navigation_and_api_follow_the_settings(): void
    {
        AppointmentSettings::fake(['waitlist_enabled' => false, 'waitlist_api_enabled' => false]);
        $this->assertFalse(WaitlistEntryResource::shouldRegisterNavigation());
        $this->assertFalse(WaitlistEntryResource::canAccess());

        $user = User::factory()->create(['branch_id' => $this->branch->id]);
        $this->actingAs($user)->getJson('/api/v1/waitlist')->assertNotFound();

        AppointmentSettings::fake(['waitlist_enabled' => true, 'waitlist_api_enabled' => true]);
        $this->assertTrue(WaitlistEntryResource::shouldRegisterNavigation());
        $this->assertNotSame(404, $this->actingAs($user)->getJson('/api/v1/waitlist')->getStatusCode());
    }

    public function test_recurrence_relation_manager_follows_the_setting(): void
    {
        $appointment = Appointment::factory()->create(['branch_id' => $this->branch->id]);

        AppointmentSettings::fake(['recurrence_enabled' => false]);
        $this->assertFalse(RecurrenceRulesRelationManager::canViewForRecord($appointment, EditAppointment::class));

        AppointmentSettings::fake(['recurrence_enabled' => true]);
        $this->assertTrue(RecurrenceRulesRelationManager::canViewForRecord($appointment, EditAppointment::class));
    }
}
