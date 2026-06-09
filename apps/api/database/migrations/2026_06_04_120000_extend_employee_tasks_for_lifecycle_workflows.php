<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_lifecycle_task_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('lifecycle_type', 32);
            $table->string('title', 150);
            $table->string('category', 50);
            $table->string('task_type', 50)->nullable();
            $table->string('assignee_type', 50);
            $table->boolean('requires_approval')->default(false);
            $table->string('approval_workflow_key', 100)->nullable();
            $table->integer('due_offset_days')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['company_id', 'lifecycle_type', 'is_active'], 'employee_lifecycle_templates_scope_idx');
        });

        Schema::table('employee_onboarding_tasks', function (Blueprint $table): void {
            $table->string('lifecycle_type', 32)->default('onboarding')->after('employee_id');
            $table->foreignId('template_id')->nullable()->after('lifecycle_type')->constrained('employee_lifecycle_task_templates');
            $table->foreignId('assigned_to_user_id')->nullable()->after('assignee_type')->constrained('users');
            $table->boolean('requires_approval')->default(false)->after('assigned_to_user_id');
            $table->string('approval_workflow_key', 100)->nullable()->after('requires_approval');
            $table->foreignId('workflow_instance_id')->nullable()->after('approval_workflow_key')->constrained('workflow_instances');
            $table->foreignId('completed_by_user_id')->nullable()->after('completed_at')->constrained('users');
            $table->foreignId('latest_action_by_user_id')->nullable()->after('completed_by_user_id')->constrained('users');
            $table->timestamp('approved_at')->nullable()->after('latest_action_by_user_id');
            $table->index(['employee_id', 'lifecycle_type', 'status'], 'employee_lifecycle_tasks_employee_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::table('employee_onboarding_tasks', function (Blueprint $table): void {
            $table->dropIndex('employee_lifecycle_tasks_employee_scope_idx');
            $table->dropConstrainedForeignId('template_id');
            $table->dropConstrainedForeignId('assigned_to_user_id');
            $table->dropConstrainedForeignId('workflow_instance_id');
            $table->dropConstrainedForeignId('completed_by_user_id');
            $table->dropConstrainedForeignId('latest_action_by_user_id');
            $table->dropColumn([
                'lifecycle_type',
                'requires_approval',
                'approval_workflow_key',
                'approved_at',
            ]);
        });

        Schema::dropIfExists('employee_lifecycle_task_templates');
    }
};
