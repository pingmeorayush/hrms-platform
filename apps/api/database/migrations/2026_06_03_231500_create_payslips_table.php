<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_compensation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slip_number', 100);
            $table->string('status', 20)->default('generated');
            $table->string('currency', 10);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('payroll_date');
            $table->string('file_name', 255);
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->decimal('employer_cost', 12, 2)->default(0);
            $table->json('earnings_breakdown')->nullable();
            $table->json('deductions_breakdown')->nullable();
            $table->json('employer_contribution_breakdown')->nullable();
            $table->json('employee_snapshot')->nullable();
            $table->json('company_snapshot')->nullable();
            $table->string('rendered_format', 20)->default('html');
            $table->longText('rendered_content');
            $table->string('checksum_sha256', 64);
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'slip_number']);
            $table->unique(['payroll_run_id', 'employee_id']);
            $table->index(['company_id', 'employee_id', 'generated_at'], 'payslips_employee_generated_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
