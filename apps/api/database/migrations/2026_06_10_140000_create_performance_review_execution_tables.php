<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performance_review_cycle_id')->constrained('performance_review_cycles')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('manager_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->json('reviewer_user_ids');
            $table->json('goal_snapshot');
            $table->json('competency_snapshot');
            $table->json('visibility_rules');
            $table->string('status', 32)->default('draft');
            $table->timestamp('launched_at')->nullable();
            $table->timestamp('self_submitted_at')->nullable();
            $table->timestamp('manager_submitted_at')->nullable();
            $table->timestamp('calibration_completed_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('reopened_at')->nullable();
            $table->unsignedInteger('reopen_count')->default(0);
            $table->text('reopened_reason')->nullable();
            $table->json('calibration_payload')->nullable();
            $table->json('final_payload')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'performance_review_cycle_id', 'employee_id'], 'performance_reviews_company_cycle_employee_unique');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'employee_id']);
            $table->index(['company_id', 'manager_employee_id']);
        });

        Schema::create('performance_review_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performance_review_id')->constrained('performance_reviews')->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('role_type', 32);
            $table->json('visibility_scope');
            $table->json('section_payload');
            $table->json('competency_payload')->nullable();
            $table->decimal('overall_rating', 5, 2)->nullable();
            $table->text('summary')->nullable();
            $table->text('confidential_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['performance_review_id', 'reviewer_user_id', 'role_type'], 'performance_review_submissions_unique_reviewer_role');
            $table->index(['company_id', 'role_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_review_submissions');
        Schema::dropIfExists('performance_reviews');
    }
};
