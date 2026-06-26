<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 64);
            $table->string('delivery_mode', 32);
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->boolean('requires_completion_evidence')->default(false);
            $table->unsignedSmallInteger('renewal_frequency_months')->nullable();
            $table->unsignedSmallInteger('default_due_days')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status', 32)->default('active');
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'category']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('learning_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_item_id')->constrained('learning_items')->cascadeOnDelete();
            $table->string('assignment_code', 64);
            $table->foreignId('assigned_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('audience_type', 32);
            $table->json('audience_rules');
            $table->date('assigned_on');
            $table->date('due_on')->nullable();
            $table->json('completion_rules');
            $table->text('notes')->nullable();
            $table->string('status', 32)->default('active');
            $table->unsignedInteger('target_count')->default(0);
            $table->unsignedInteger('completion_count')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'assignment_code']);
            $table->index(['company_id', 'learning_item_id']);
            $table->index(['company_id', 'audience_type']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('learning_assignment_targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_assignment_id')->constrained('learning_assignments')->cascadeOnDelete();
            $table->foreignId('learning_item_id')->constrained('learning_items')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->date('assigned_on');
            $table->date('due_on')->nullable();
            $table->string('status', 32)->default('assigned');
            $table->unsignedTinyInteger('completion_progress_percent')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('completion_notes')->nullable();
            $table->json('completion_evidence')->nullable();
            $table->date('renewal_due_on')->nullable();
            $table->timestamps();

            $table->unique(['learning_assignment_id', 'employee_id']);
            $table->index(['company_id', 'employee_id']);
            $table->index(['company_id', 'learning_item_id']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'due_on']);
            $table->index(['company_id', 'renewal_due_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_assignment_targets');
        Schema::dropIfExists('learning_assignments');
        Schema::dropIfExists('learning_items');
    }
};
