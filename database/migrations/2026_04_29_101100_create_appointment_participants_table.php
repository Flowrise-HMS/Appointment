<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('participant_type');
            $table->string('participant_type_code')->nullable();
            $table->string('actor_reference');
            $table->boolean('required')->default(true);
            $table->string('status')->default('needs-action');
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['appointment_id', 'participant_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_participants');
    }
};
