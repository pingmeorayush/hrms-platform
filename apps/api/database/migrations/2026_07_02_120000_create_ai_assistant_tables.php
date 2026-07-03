<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->string('persona', 64)->default('employee_copilot');
            $table->string('status', 32)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamp('last_interacted_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'user_id', 'last_interacted_at']);
            $table->index(['company_id', 'persona', 'status']);
        });

        Schema::create('ai_interactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('interaction_type', 32)->default('answer');
            $table->string('use_case', 64);
            $table->text('question');
            $table->text('answer');
            $table->string('status', 32)->default('answered');
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->json('citations')->nullable();
            $table->json('guardrails')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedTinyInteger('feedback_rating')->nullable();
            $table->string('feedback_sentiment', 16)->nullable();
            $table->text('feedback_notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'user_id', 'created_at']);
            $table->index(['company_id', 'use_case', 'status']);
            $table->index(['company_id', 'ai_conversation_id', 'responded_at']);
        });

        Schema::create('ai_recommendations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ai_conversation_id')->nullable()->constrained('ai_conversations')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('scenario', 64);
            $table->string('title', 160);
            $table->text('summary');
            $table->json('rationale')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->json('suggested_actions')->nullable();
            $table->json('supporting_citations')->nullable();
            $table->string('status', 32)->default('pending_review');
            $table->boolean('human_review_required')->default(true);
            $table->string('decision', 32)->nullable();
            $table->text('decision_notes')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'user_id', 'status', 'created_at']);
            $table->index(['company_id', 'employee_id', 'scenario']);
            $table->index(['company_id', 'decision', 'decided_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
        Schema::dropIfExists('ai_interactions');
        Schema::dropIfExists('ai_conversations');
    }
};
