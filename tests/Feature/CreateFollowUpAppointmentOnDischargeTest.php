<?php

namespace Modules\Appointment\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\AppointmentType;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Providers\EventServiceProvider;
use Modules\Clinical\Classes\Services\AdtService;
use Modules\Clinical\Events\PatientDischarged;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Location;
use Modules\Patient\Models\Patient;
use Modules\Staff\Models\Staff;
use Tests\TestCase;

class CreateFollowUpAppointmentOnDischargeTest extends TestCase
{
    use DatabaseTransactions;

    protected Branch $branch;

    protected Patient $patient;

    protected Location $bed;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateModules(['Core', 'Patient', 'Staff', 'Clinical', 'Appointment']);
        config(['clinical.discharge.enforce_readiness' => false]);

        $this->branch = Branch::factory()->default()->create();
        $this->actingAs(User::factory()->create(['branch_id' => $this->branch->id]));

        $this->patient = Patient::withoutEvents(fn () => Patient::factory()->male()->create(['branch_id' => $this->branch->id]));
        $ward = Location::factory()->room()->create(['branch_id' => $this->branch->id, 'is_active' => true]);
        $this->bed = Location::factory()->bed()->create(['branch_id' => $this->branch->id, 'parent_id' => $ward->id, 'is_active' => true]);
    }

    public function test_listener_is_registered_softly_against_the_clinical_event(): void
    {
        $listens = (new EventServiceProvider($this->app))->listens();

        $this->assertArrayHasKey(PatientDischarged::class, $listens);
    }

    public function test_discharge_with_a_follow_up_books_an_appointment_once(): void
    {
        $encounter = app(AdtService::class)->admit($this->patient, $this->bed->id);
        $provider = Staff::factory()->create(['branch_id' => $this->branch->id]);
        $when = now()->addDays(7)->setTime(10, 0);

        app(AdtService::class)->discharge($encounter, followUpAt: $when, followUpProviderId: $provider->id);

        $appointment = Appointment::query()->where('patient_id', $this->patient->id)->first();

        $this->assertNotNull($appointment, 'a follow-up appointment was expected');
        $this->assertSame(AppointmentType::FOLLOW_UP, $appointment->appointment_type);
        $this->assertSame(AppointmentStatus::BOOKED, $appointment->status);
        $this->assertSame($provider->id, $appointment->practitioner_primary_id);
        $this->assertTrue($when->equalTo($appointment->start_at));
        $this->assertSame(20, (int) $appointment->start_at->diffInMinutes($appointment->end_at));
        $this->assertSame('discharge-follow-up:'.$encounter->id, $appointment->idempotency_key);

        // Replaying the event must not double-book.
        event(new PatientDischarged($encounter->fresh(), $encounter->locationEvents()->latest('occurred_at')->first(), $when, $provider->id));

        $this->assertSame(1, Appointment::query()->where('patient_id', $this->patient->id)->count());
    }

    public function test_discharge_without_a_follow_up_books_nothing(): void
    {
        $encounter = app(AdtService::class)->admit($this->patient, $this->bed->id);

        app(AdtService::class)->discharge($encounter);

        $this->assertSame(0, Appointment::query()->where('patient_id', $this->patient->id)->count());
    }
}
