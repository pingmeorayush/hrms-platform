<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_hire_handoffs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_requisition_id')->constrained('job_requisitions')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('recruiter_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('converted_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('source_resume_id')->nullable()->constrained('candidate_resumes')->cascadeOnUpdate()->nullOnDelete();
            $table->string('status', 40)->default('employee_created');
            $table->json('offer_snapshot');
            $table->json('candidate_snapshot');
            $table->json('requisition_snapshot');
            $table->json('document_references')->nullable();
            $table->json('onboarding_template_ids')->nullable();
            $table->json('onboarding_task_ids')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('converted_at');
            $table->timestamp('onboarding_triggered_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'offer_id'], 'recruitment_handoffs_company_offer_uniq');
            $table->index(['company_id', 'candidate_id'], 'recruitment_handoffs_candidate_idx');
            $table->index(['company_id', 'employee_id'], 'recruitment_handoffs_employee_idx');
            $table->index(['company_id', 'status'], 'recruitment_handoffs_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_hire_handoffs');
    }
};
