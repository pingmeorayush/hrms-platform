<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_onboarding_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->string('category', 50);
            $table->string('task_type', 50)->nullable();
            $table->string('assignee_type', 50);
            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('sort_order')->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(
                ['company_id', 'employee_id', 'status'],
                'employee_onboarding_tasks_company_employee_status_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_onboarding_tasks');
    }
};
