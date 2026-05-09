<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action');
            $table->json('before_payload')->nullable();
            $table->json('after_payload')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['branch_id', 'action', 'occurred_at']);
            $table->index(['appointment_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_audits');
    }
};
