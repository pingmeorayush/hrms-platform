<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_competencies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('name');
            $table->string('category', 64);
            $table->text('description')->nullable();
            $table->json('scale_definition');
            $table->string('status', 32)->default('active');
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'category']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('performance_review_cycles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('name');
            $table->string('cycle_type', 32);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->date('self_review_due_on')->nullable();
            $table->date('manager_review_due_on')->nullable();
            $table->date('calibration_starts_on')->nullable();
            $table->date('calibration_ends_on')->nullable();
            $table->date('publish_on')->nullable();
            $table->json('participant_rules');
            $table->json('review_template');
            $table->json('competency_visibility');
            $table->string('status', 32)->default('draft');
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'cycle_type']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('performance_goals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performance_review_cycle_id')->nullable()->constrained('performance_review_cycles')->nullOnDelete();
            $table->foreignId('owner_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('goal_code', 64);
            $table->string('goal_type', 32)->default('library');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_on');
            $table->decimal('weight_percent', 5, 2);
            $table->json('success_metric')->nullable();
            $table->string('status', 32)->default('draft');
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'goal_code']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'owner_employee_id']);
            $table->index(['company_id', 'performance_review_cycle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_goals');
        Schema::dropIfExists('performance_review_cycles');
        Schema::dropIfExists('performance_competencies');
    }
};
