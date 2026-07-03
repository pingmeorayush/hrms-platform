<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resilience_validation_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('scenario_key', 64);
            $table->string('scenario_name', 120);
            $table->string('scenario_type', 32);
            $table->string('environment', 64);
            $table->string('status', 32);
            $table->unsignedInteger('recovery_point_actual_minutes')->nullable();
            $table->unsignedInteger('recovery_time_actual_minutes')->nullable();
            $table->json('evidence_refs')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('executed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'scenario_key', 'completed_at']);
            $table->index(['company_id', 'status', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resilience_validation_runs');
    }
};
