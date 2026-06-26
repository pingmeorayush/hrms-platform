<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_requisition_id')->constrained('job_requisitions')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('interviewer_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('interview_code', 50);
            $table->unsignedSmallInteger('round_number')->default(1);
            $table->string('interview_type', 30);
            $table->string('status', 30)->default('scheduled');
            $table->string('timezone', 100);
            $table->timestamp('scheduled_start_at');
            $table->timestamp('scheduled_end_at');
            $table->string('meeting_mode', 30)->default('virtual');
            $table->string('meeting_location', 255)->nullable();
            $table->string('meeting_link', 500)->nullable();
            $table->text('agenda')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'interview_code'], 'interviews_company_code_uniq');
            $table->index(['company_id', 'candidate_id', 'scheduled_start_at'], 'interviews_candidate_time_idx');
            $table->index(['company_id', 'interviewer_user_id', 'scheduled_start_at'], 'interviews_interviewer_time_idx');
            $table->index(['company_id', 'status', 'scheduled_start_at'], 'interviews_status_time_idx');
        });

        Schema::create('interview_feedback', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interview_id')->constrained('interviews')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('job_requisition_id')->constrained('job_requisitions')->cascadeOnDelete();
            $table->foreignId('interviewer_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedTinyInteger('rating')->default(3);
            $table->string('recommendation', 30);
            $table->text('comments');
            $table->text('strengths')->nullable();
            $table->text('concerns')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(['interview_id'], 'interview_feedback_interview_uniq');
            $table->index(['company_id', 'candidate_id', 'submitted_at'], 'interview_feedback_candidate_time_idx');
            $table->index(['company_id', 'job_requisition_id', 'submitted_at'], 'interview_feedback_requisition_time_idx');
        });

        Schema::create('offers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_requisition_id')->constrained('job_requisitions')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('recruiter_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('workflow_instance_id')->nullable()->constrained('workflow_instances')->cascadeOnUpdate()->nullOnDelete();
            $table->string('offer_code', 50);
            $table->string('status', 30)->default('draft');
            $table->string('employment_type', 30);
            $table->string('currency', 10)->default('INR');
            $table->decimal('annual_ctc_amount', 14, 2);
            $table->decimal('joining_bonus_amount', 14, 2)->nullable();
            $table->date('proposed_start_date')->nullable();
            $table->date('expires_on');
            $table->text('notes')->nullable();
            $table->text('candidate_message')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'offer_code'], 'offers_company_code_uniq');
            $table->index(['company_id', 'candidate_id', 'status'], 'offers_candidate_status_idx');
            $table->index(['company_id', 'job_requisition_id', 'status'], 'offers_requisition_status_idx');
            $table->index(['company_id', 'recruiter_user_id', 'status'], 'offers_recruiter_status_idx');
            $table->index(['company_id', 'expires_on', 'status'], 'offers_expiry_status_idx');
        });

        Schema::create('offer_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('decision_type', 40);
            $table->text('comment')->nullable();
            $table->foreignId('acted_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('acted_at');
            $table->timestamps();

            $table->index(['company_id', 'offer_id', 'acted_at'], 'offer_decisions_offer_time_idx');
            $table->index(['company_id', 'to_status'], 'offer_decisions_to_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_decisions');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('interview_feedback');
        Schema::dropIfExists('interviews');
    }
};
