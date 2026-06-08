<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_accruals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_policy_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('policy_version');
            $table->string('accrual_frequency', 30);
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('opening_balance_days', 8, 2)->default(0);
            $table->decimal('accrued_days', 8, 2)->default(0);
            $table->decimal('carry_forward_days', 8, 2)->default(0);
            $table->decimal('encashable_days', 8, 2)->default(0);
            $table->decimal('used_days_in_period', 8, 2)->default(0);
            $table->decimal('projected_closing_balance_days', 8, 2)->default(0);
            $table->boolean('is_eligible')->default(true);
            $table->string('calculation_hash', 64);
            $table->string('status', 30)->default('projected');
            $table->json('eligibility_snapshot');
            $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'calculation_hash'], 'leave_accruals_calc_hash_unique');
            $table->index(['company_id', 'employee_id', 'period_start'], 'leave_accruals_employee_period_idx');
            $table->index(['company_id', 'leave_policy_id'], 'leave_accruals_policy_idx');
        });

        Schema::create('leave_encashments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_accrual_id')->constrained('leave_accruals')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_policy_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('policy_version');
            $table->date('cycle_start');
            $table->date('cycle_end');
            $table->decimal('projected_days', 8, 2)->default(0);
            $table->string('status', 30)->default('projected');
            $table->json('metadata')->nullable();
            $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'leave_accrual_id'], 'leave_encashments_accrual_unique');
            $table->index(['company_id', 'employee_id', 'cycle_start'], 'leave_encashments_employee_cycle_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_encashments');
        Schema::dropIfExists('leave_accruals');
    }
};
