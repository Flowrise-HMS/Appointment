<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('resource_type');
            $table->string('resource_reference');
            $table->timestamp('allocated_from');
            $table->timestamp('allocated_to');
            $table->timestamps();

            $table->index(['branch_id', 'resource_type'], 'appt_res_branch_type_idx');
            $table->index(['resource_reference', 'allocated_from', 'allocated_to'], 'appt_res_ref_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_resources');
    }
};
