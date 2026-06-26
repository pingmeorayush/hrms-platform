<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_requisition_id')->constrained('job_requisitions')->cascadeOnDelete();
            $table->string('candidate_code', 50);
            $table->foreignId('recruiter_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email', 150);
            $table->string('phone', 50)->nullable();
            $table->string('source', 50)->default('manual');
            $table->string('current_stage', 30)->default('applied');
            $table->string('status', 30)->default('active');
            $table->timestamp('stage_entered_at')->nullable();
            $table->decimal('total_experience_years', 4, 1)->nullable();
            $table->unsignedInteger('notice_period_days')->nullable();
            $table->string('current_company', 150)->nullable();
            $table->string('current_title', 150)->nullable();
            $table->text('summary')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'candidate_code'], 'candidates_company_code_uniq');
            $table->unique(['company_id', 'email'], 'candidates_company_email_uniq');
            $table->index(['company_id', 'job_requisition_id', 'status'], 'candidates_requisition_status_idx');
            $table->index(['company_id', 'recruiter_user_id', 'current_stage'], 'candidates_recruiter_stage_idx');
            $table->index(['company_id', 'current_stage', 'status'], 'candidates_stage_status_idx');
        });

        Schema::create('candidate_resumes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->boolean('is_current')->default(true);
            $table->string('original_file_name', 255);
            $table->string('disk', 50);
            $table->string('file_path', 255);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('checksum_sha256', 64);
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['candidate_id', 'version_number'], 'candidate_resumes_candidate_version_uniq');
            $table->index(['company_id', 'candidate_id', 'is_current'], 'candidate_resumes_current_idx');
        });

        Schema::create('candidate_stage_transitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->string('from_stage', 30)->nullable();
            $table->string('to_stage', 30);
            $table->string('resulting_status', 30);
            $table->text('comment')->nullable();
            $table->foreignId('transitioned_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('transitioned_at');
            $table->timestamps();

            $table->index(['company_id', 'candidate_id', 'transitioned_at'], 'candidate_stage_transitions_candidate_time_idx');
            $table->index(['company_id', 'to_stage'], 'candidate_stage_transitions_to_stage_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_stage_transitions');
        Schema::dropIfExists('candidate_resumes');
        Schema::dropIfExists('candidates');
    }
};
