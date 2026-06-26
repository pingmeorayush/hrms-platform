<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_requisitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('requisition_code', 50);
            $table->string('title', 150);
            $table->string('employment_type', 30);
            $table->string('hiring_type', 30);
            $table->string('priority', 20)->default('medium');
            $table->unsignedSmallInteger('openings_count')->default(1);
            $table->decimal('min_experience_years', 4, 1)->nullable();
            $table->date('target_start_date')->nullable();
            $table->string('headcount_reference', 100)->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->foreignId('recruiter_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('hiring_manager_employee_id')->constrained('employees')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('workflow_instance_id')->nullable()->constrained('workflow_instances')->cascadeOnUpdate()->nullOnDelete();
            $table->string('status', 30)->default('draft');
            $table->string('status_before_hold', 30)->nullable();
            $table->text('justification');
            $table->text('notes')->nullable();
            $table->text('closed_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('on_hold_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'requisition_code'], 'job_requisitions_company_code_uniq');
            $table->index(['company_id', 'status', 'created_at'], 'job_requisitions_status_created_idx');
            $table->index(['company_id', 'recruiter_user_id', 'status'], 'job_requisitions_recruiter_status_idx');
            $table->index(['company_id', 'hiring_manager_employee_id', 'status'], 'job_requisitions_manager_status_idx');
            $table->index(['company_id', 'department_id', 'status'], 'job_requisitions_department_status_idx');
            $table->index(['company_id', 'submitted_at'], 'job_requisitions_submitted_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_requisitions');
    }
};
