<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_waitlist_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->restrictOnDelete();
            $table->uuid('preferred_practitioner_id')->nullable();
            $table->foreignUuid('preferred_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignUuid('preferred_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->unsignedTinyInteger('urgency_score')->default(1);
            $table->unsignedSmallInteger('wait_time_score')->default(1);
            $table->unsignedTinyInteger('referral_score')->default(1);
            $table->unsignedTinyInteger('manual_override_score')->default(0);
            $table->unsignedSmallInteger('computed_priority_score')->default(3);
            $table->string('status')->default('waiting');
            $table->timestamps();

            $table->index(['branch_id', 'computed_priority_score', 'created_at'], 'appt_wait_branch_score_idx');
            $table->index(['patient_id', 'status'], 'appt_wait_patient_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_waitlist_entries');
    }
};
