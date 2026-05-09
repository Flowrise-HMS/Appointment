<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_sync_outbox', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('aggregate_type')->default('appointment');
            $table->uuid('aggregate_id');
            $table->string('event_name');
            $table->string('idempotency_key')->unique();
            $table->json('payload');
            $table->timestamp('available_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->string('status')->default('pending');
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status', 'available_at']);
            $table->index(['aggregate_type', 'aggregate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_sync_outbox');
    }
};
