<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_runs', function (Blueprint $table): void {
            $table->json('calculation_summary')->nullable()->after('input_summary');
            $table->timestamp('calculated_at')->nullable()->after('inputs_generated_at');
            $table->timestamp('approved_at')->nullable()->after('calculated_at');
            $table->timestamp('locked_at')->nullable()->after('approved_at');
            $table->timestamp('reopened_at')->nullable()->after('locked_at');
        });

        Schema::create('payroll_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_compensation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20);
            $table->decimal('employment_days', 8, 2)->default(0);
            $table->decimal('unpaid_days', 8, 2)->default(0);
            $table->decimal('lop_days', 8, 2)->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->decimal('overtime_earnings', 12, 2)->default(0);
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->decimal('employer_cost', 12, 2)->default(0);
            $table->json('earnings_breakdown')->nullable();
            $table->json('deductions_breakdown')->nullable();
            $table->json('employer_contribution_breakdown')->nullable();
            $table->json('input_snapshot')->nullable();
            $table->json('validation_errors')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'payroll_run_id', 'employee_id'], 'payroll_items_run_employee_idx');
            $table->index(['company_id', 'status'], 'payroll_items_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');

        Schema::table('payroll_runs', function (Blueprint $table): void {
            $table->dropColumn([
                'calculation_summary',
                'calculated_at',
                'approved_at',
                'locked_at',
                'reopened_at',
            ]);
        });
    }
};
