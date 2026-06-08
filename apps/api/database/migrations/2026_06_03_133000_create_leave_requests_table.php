<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_policy_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('policy_version');
            $table->foreignId('workflow_instance_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 8, 2);
            $table->string('status', 30)->default('pending');
            $table->text('reason');
            $table->text('approver_comment')->nullable();
            $table->boolean('is_auto_approved')->default(false);
            $table->string('attendance_sync_status', 30)->default('not_applicable');
            $table->timestamp('attendance_synced_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'employee_id', 'status'], 'leave_requests_employee_status_idx');
            $table->index(['company_id', 'start_date', 'end_date'], 'leave_requests_date_span_idx');
            $table->index(['company_id', 'leave_type_id', 'status'], 'leave_requests_type_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
