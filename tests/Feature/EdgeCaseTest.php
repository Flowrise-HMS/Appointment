<?php

namespace Modules\Appointment\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Modules\Appointment\Enums\AppointmentParticipantStatus;
use Modules\Appointment\Enums\AppointmentStatus;
use Modules\Appointment\Enums\AppointmentType;
use Modules\Appointment\Enums\WaitlistEntryStatus;
use Modules\Appointment\Models\Appointment;
use Modules\Appointment\Models\AppointmentParticipant;
use Modules\Appointment\Models\AppointmentRecurrenceRule;
use Modules\Appointment\Models\WaitlistEntry;
use Tests\TestCase;

class EdgeCaseTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateModules(['Core', 'Patient', 'Appointment']);
    }

    // ─── AppointmentStatus enum edge cases ───────────────────────────────────

    public function test_appointment_status_allows_check_in(): void
    {
        $this->assertTrue(AppointmentStatus::BOOKED->allowsCheckIn());
        $this->assertTrue(AppointmentStatus::PENDING->allowsCheckIn());
        $this->assertFalse(AppointmentStatus::PROPOSED->allowsCheckIn());
        $this->assertFalse(AppointmentStatus::ARRIVED->allowsCheckIn());
        $this->assertFalse(AppointmentStatus::FULFILLED->allowsCheckIn());
        $this->assertFalse(AppointmentStatus::CANCELLED->allowsCheckIn());
        $this->assertFalse(AppointmentStatus::NOSHOW->allowsCheckIn());
    }

    public function test_appointment_status_allows_cancellation(): void
    {
        $this->assertTrue(AppointmentStatus::PROPOSED->allowsCancellation());
        $this->assertTrue(AppointmentStatus::PENDING->allowsCancellation());
        $this->assertTrue(AppointmentStatus::BOOKED->allowsCancellation());
        $this->assertTrue(AppointmentStatus::ARRIVED->allowsCancellation());
        $this->assertFalse(AppointmentStatus::FULFILLED->allowsCancellation());
        $this->assertFalse(AppointmentStatus::CANCELLED->allowsCancellation());
        $this->assertTrue(AppointmentStatus::NOSHOW->allowsCancellation());
    }

    public function test_appointment_status_is_terminal(): void
    {
        $this->assertTrue(AppointmentStatus::CANCELLED->isTerminal());
        $this->assertTrue(AppointmentStatus::FULFILLED->isTerminal());
        $this->assertTrue(AppointmentStatus::NOSHOW->isTerminal());
        $this->assertFalse(AppointmentStatus::PROPOSED->isTerminal());
        $this->assertFalse(AppointmentStatus::PENDING->isTerminal());
        $this->assertFalse(AppointmentStatus::BOOKED->isTerminal());
        $this->assertFalse(AppointmentStatus::ARRIVED->isTerminal());
    }

    public function test_appointment_status_values_and_default(): void
    {
        $values = AppointmentStatus::values();
        $this->assertContains('proposed', $values);
        $this->assertContains('pending', $values);
        $this->assertContains('booked', $values);
        $this->assertContains('arrived', $values);
        $this->assertContains('fulfilled', $values);
        $this->assertContains('cancelled', $values);
        $this->assertContains('noshow', $values);
        $this->assertCount(7, $values);
        $this->assertSame(AppointmentStatus::BOOKED, AppointmentStatus::default());
    }

    public function test_appointment_status_blocking_conflict_statuses(): void
    {
        $blocking = AppointmentStatus::blockingConflictStatuses();
        $this->assertContains(AppointmentStatus::PROPOSED, $blocking);
        $this->assertContains(AppointmentStatus::PENDING, $blocking);
        $this->assertContains(AppointmentStatus::BOOKED, $blocking);
        $this->assertContains(AppointmentStatus::ARRIVED, $blocking);
        $this->assertNotContains(AppointmentStatus::FULFILLED, $blocking);
        $this->assertNotContains(AppointmentStatus::CANCELLED, $blocking);
        $this->assertNotContains(AppointmentStatus::NOSHOW, $blocking);
    }

    public function test_appointment_status_active_patient_access_statuses(): void
    {
        $active = AppointmentStatus::activePatientAccessStatuses();
        $this->assertContains(AppointmentStatus::PENDING, $active);
        $this->assertContains(AppointmentStatus::BOOKED, $active);
        $this->assertContains(AppointmentStatus::ARRIVED, $active);
        $this->assertNotContains(AppointmentStatus::CANCELLED, $active);
        $this->assertNotContains(AppointmentStatus::FULFILLED, $active);
        $this->assertNotContains(AppointmentStatus::NOSHOW, $active);
    }

    public function test_appointment_status_labels_and_colors(): void
    {
        $this->assertSame('Booked', AppointmentStatus::BOOKED->getLabel());
        $this->assertSame('Cancelled', AppointmentStatus::CANCELLED->getLabel());
        $this->assertSame('No-show', AppointmentStatus::NOSHOW->getLabel());
        $this->assertSame('danger', AppointmentStatus::CANCELLED->getColor());
        $this->assertSame('success', AppointmentStatus::FULFILLED->getColor());
    }

    public function test_appointment_type_enum(): void
    {
        $this->assertCount(8, AppointmentType::cases());
        $values = AppointmentType::values();
        $this->assertContains('outpatient', $values);
        $this->assertContains('inpatient', $values);
        $this->assertContains('emergency', $values);
        $this->assertContains('virtual', $values);
        $this->assertContains('follow_up', $values);
        $this->assertContains('procedure', $values);
        $this->assertContains('screening', $values);
        $this->assertContains('consult', $values);
    }

    // ─── AppointmentParticipantStatus enum edge cases ────────────────────────

    public function test_participant_status_values_and_default(): void
    {
        $values = AppointmentParticipantStatus::values();
        $this->assertContains('needs-action', $values);
        $this->assertContains('accepted', $values);
        $this->assertContains('declined', $values);
        $this->assertContains('tentative', $values);
        $this->assertCount(4, $values);
        $this->assertSame(AppointmentParticipantStatus::NEEDS_ACTION, AppointmentParticipantStatus::default());
    }

    public function test_participant_status_labels_and_colors(): void
    {
        $this->assertSame('Accepted', AppointmentParticipantStatus::ACCEPTED->getLabel());
        $this->assertSame('Declined', AppointmentParticipantStatus::DECLINED->getLabel());
        $this->assertSame('success', AppointmentParticipantStatus::ACCEPTED->getColor());
        $this->assertSame('danger', AppointmentParticipantStatus::DECLINED->getColor());
    }

    // ─── WaitlistEntryStatus enum edge cases ────────────────────────────────

    public function test_waitlist_entry_status_values_and_default(): void
    {
        $values = WaitlistEntryStatus::values();
        $this->assertContains('waiting', $values);
        $this->assertContains('offered', $values);
        $this->assertContains('booked', $values);
        $this->assertContains('declined', $values);
        $this->assertContains('expired', $values);
        $this->assertContains('cancelled', $values);
        $this->assertCount(6, $values);
        $this->assertSame(WaitlistEntryStatus::WAITING, WaitlistEntryStatus::default());
    }

    public function test_waitlist_entry_status_labels_and_colors(): void
    {
        $this->assertSame('Waiting', WaitlistEntryStatus::WAITING->getLabel());
        $this->assertSame('Slot offered', WaitlistEntryStatus::OFFERED->getLabel());
        $this->assertSame('Expired', WaitlistEntryStatus::EXPIRED->getLabel());
        $this->assertSame('warning', WaitlistEntryStatus::WAITING->getColor());
        $this->assertSame('danger', WaitlistEntryStatus::CANCELLED->getColor());
    }

    // ─── Appointment model validation / constraint edge cases ────────────────

    public function test_appointment_belongs_to_patient(): void
    {
        $appointment = Appointment::factory()->create();
        $this->assertNotNull($appointment->patient);
    }

    public function test_appointment_casts_status_as_enum(): void
    {
        $appointment = Appointment::factory()->create(['status' => AppointmentStatus::PROPOSED]);
        $this->assertTrue($appointment->status instanceof AppointmentStatus);
        $this->assertSame(AppointmentStatus::PROPOSED, $appointment->status);
    }

    public function test_appointment_casts_datetime_fields(): void
    {
        $appointment = Appointment::factory()->create();
        $this->assertInstanceOf(Carbon::class, $appointment->start_at);
        $this->assertInstanceOf(Carbon::class, $appointment->end_at);
    }

    // ─── Appointment participant relationship ────────────────────────────────

    public function test_appointment_has_many_participants(): void
    {
        $appointment = Appointment::factory()->create();
        AppointmentParticipant::factory()->create(['appointment_id' => $appointment->id]);
        AppointmentParticipant::factory()->create(['appointment_id' => $appointment->id]);
        $this->assertCount(2, $appointment->refresh()->participants);
    }

    public function test_participant_belongs_to_appointment(): void
    {
        $participant = AppointmentParticipant::factory()->create();
        $this->assertNotNull($participant->appointment);
    }

    // ─── Appointment recurrence rule relationship ────────────────────────────

    public function test_appointment_has_many_recurrence_rules(): void
    {
        $appointment = Appointment::factory()->create();
        $rule = AppointmentRecurrenceRule::factory()->create(['appointment_id' => $appointment->id]);
        $this->assertTrue($appointment->recurrenceRules->contains($rule));
    }

    // ─── Soft delete behavior ────────────────────────────────────────────────

    public function test_appointment_soft_deletes(): void
    {
        $appointment = Appointment::factory()->create();
        $id = $appointment->id;
        $appointment->delete();
        $this->assertNull(Appointment::find($id));
        $this->assertNotNull(Appointment::withTrashed()->find($id));
    }

    // ─── WaitlistEntry model edge cases ──────────────────────────────────────

    public function test_waitlist_entry_belongs_to_patient(): void
    {
        $entry = WaitlistEntry::factory()->create();
        $this->assertNotNull($entry->patient);
    }

    public function test_waitlist_entry_status_cast(): void
    {
        $entry = WaitlistEntry::factory()->create(['status' => WaitlistEntryStatus::WAITING]);
        $this->assertTrue($entry->status instanceof WaitlistEntryStatus);
        $this->assertSame(WaitlistEntryStatus::WAITING, $entry->status);
    }
}
