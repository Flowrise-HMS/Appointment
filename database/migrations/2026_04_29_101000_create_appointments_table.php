<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('patient_id')->constrained('patients')->restrictOnDelete();
            $table->uuid('practitioner_primary_id')->nullable();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('status')->default('booked');
            $table->string('appointment_type')->nullable();
            $table->unsignedTinyInteger('priority')->default(5);
            $table->string('service_category_code')->nullable();
            $table->string('service_type_code')->nullable();
            $table->string('reason_code')->nullable();
            $table->text('reason_text')->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('cancellation_reason_code')->nullable();
            $table->string('external_reference')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->text('notes_encrypted')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index('branch_id');
            $table->index('patient_id');
            $table->index('practitioner_primary_id');
            $table->index('status');
            $table->index('start_at');
            $table->index(['branch_id', 'start_at', 'status']);
            $table->index(['practitioner_primary_id', 'start_at', 'status']);
            $table->unique(['branch_id', 'external_reference']);
            $table->unique('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
