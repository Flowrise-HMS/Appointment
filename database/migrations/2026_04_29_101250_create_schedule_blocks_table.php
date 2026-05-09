<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_schedule_blocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->uuid('practitioner_id')->nullable();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('resource_reference')->nullable();
            $table->string('reason')->nullable();
            $table->timestamp('blocked_from');
            $table->timestamp('blocked_to');
            $table->timestamps();

            $table->index(['branch_id', 'blocked_from', 'blocked_to'], 'appt_sched_block_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_schedule_blocks');
    }
};
