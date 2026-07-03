<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('release_readiness_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('release_window_label', 120);
            $table->string('target_environment', 64);
            $table->string('decision_status', 32);
            $table->string('summary', 255);
            $table->json('blockers')->nullable();
            $table->json('artifact_refs')->nullable();
            $table->json('checklist_snapshot')->nullable();
            $table->text('decision_notes')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'target_environment', 'decided_at']);
            $table->index(['company_id', 'decision_status', 'decided_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('release_readiness_decisions');
    }
};
