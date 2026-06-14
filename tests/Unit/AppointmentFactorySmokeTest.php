<?php

namespace Modules\Appointment\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Appointment\Models\AppointmentAudit;
use Modules\Appointment\Models\AppointmentParticipant;
use Modules\Appointment\Models\AppointmentRecurrenceRule;
use Modules\Appointment\Models\AppointmentResource;
use Modules\Appointment\Models\AppointmentSyncOutbox;
use Modules\Appointment\Models\ScheduleBlock;
use Modules\Appointment\Models\WaitlistEntry;
use Tests\TestCase;

class AppointmentFactorySmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('module:migrate', ['module' => 'Core', '--force' => true]);
        $this->artisan('module:migrate', ['module' => 'Patient', '--force' => true]);
        $this->artisan('module:migrate', ['module' => 'Appointment', '--force' => true]);
    }

    public function test_appointment_audit_factory(): void
    {
        $audit = AppointmentAudit::factory()->create();
        $this->assertTrue($audit->exists);
    }

    public function test_appointment_participant_factory(): void
    {
        $participant = AppointmentParticipant::factory()->create();
        $this->assertTrue($participant->exists);
    }

    public function test_appointment_recurrence_rule_factory(): void
    {
        $rule = AppointmentRecurrenceRule::factory()->create();
        $this->assertTrue($rule->exists);
    }

    public function test_appointment_resource_factory(): void
    {
        $resource = AppointmentResource::factory()->create();
        $this->assertTrue($resource->exists);
    }

    public function test_appointment_sync_outbox_factory(): void
    {
        $outbox = AppointmentSyncOutbox::factory()->create();
        $this->assertTrue($outbox->exists);
    }

    public function test_schedule_block_factory(): void
    {
        $block = ScheduleBlock::factory()->create();
        $this->assertTrue($block->exists);
    }

    public function test_waitlist_entry_factory(): void
    {
        $entry = WaitlistEntry::factory()->create();
        $this->assertTrue($entry->exists);
    }
}
