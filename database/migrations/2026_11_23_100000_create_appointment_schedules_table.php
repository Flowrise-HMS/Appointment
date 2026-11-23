<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Availability schedules (practitioner / service / location) and the bookable
 * slots generated from them. Modelled after FHIR Schedule and Slot, but no FHIR
 * endpoints are exposed yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('name');
            // Staff is an optional module, so no FK (same as schedule blocks).
            $table->uuid('practitioner_id')->nullable();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignUuid('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('resource_reference')->nullable();
            $table->unsignedSmallInteger('slot_minutes')->default(15);
            $table->date('planning_horizon_start')->nullable();
            $table->date('planning_horizon_end')->nullable();
            $table->unsignedSmallInteger('generation_days')->default(28);
            $table->string('timezone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'practitioner_id', 'is_active'], 'appt_sched_practitioner_idx');
            $table->index(['branch_id', 'service_id', 'is_active'], 'appt_sched_service_idx');
        });

        Schema::create('appointment_schedule_availabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('schedule_id')->constrained('appointment_schedules')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // ISO-8601: 1 = Monday .. 7 = Sunday
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->index(['schedule_id', 'weekday']);
        });

        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('schedule_id')->constrained('appointment_schedules')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            // Denormalised from the schedule for availability queries.
            $table->uuid('practitioner_id')->nullable();
            $table->uuid('service_id')->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->string('status')->default('free');
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->timestamps();

            $table->unique(['schedule_id', 'start_at']);
            $table->index(['branch_id', 'practitioner_id', 'start_at'], 'appt_slot_practitioner_idx');
            $table->index(['branch_id', 'service_id', 'status', 'start_at'], 'appt_slot_service_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_slots');
        Schema::dropIfExists('appointment_schedule_availabilities');
        Schema::dropIfExists('appointment_schedules');
    }
};
