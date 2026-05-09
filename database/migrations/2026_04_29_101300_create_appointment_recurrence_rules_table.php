<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_recurrence_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('frequency');
            $table->unsignedSmallInteger('interval')->default(1);
            $table->json('by_day')->nullable();
            $table->unsignedInteger('occurrence_count')->nullable();
            $table->timestamp('until_at')->nullable();
            $table->string('timezone')->default('UTC');
            $table->timestamps();

            $table->index(['branch_id', 'frequency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_recurrence_rules');
    }
};
