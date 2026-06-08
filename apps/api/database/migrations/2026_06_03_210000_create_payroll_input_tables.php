<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_runs', function (Blueprint $table): void {
            $table->json('input_summary')->nullable()->after('prerequisite_summary');
            $table->timestamp('inputs_generated_at')->nullable()->after('prepared_at');
        });

        Schema::create('payroll_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('adjustment_code', 40);
            $table->string('name', 120);
            $table->string('category', 30);
            $table->decimal('amount', 12, 2);
            $table->date('effective_date');
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'payroll_run_id', 'employee_id'], 'payroll_adjustments_run_employee_idx');
            $table->index(['company_id', 'status'], 'payroll_adjustments_status_idx');
        });

        Schema::create('payroll_inputs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_compensation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payroll_adjustment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_type', 40);
            $table->string('input_code', 80);
            $table->string('unit', 20)->nullable();
            $table->decimal('quantity', 12, 2)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->date('effective_date')->nullable();
            $table->unsignedBigInteger('source_record_id')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'payroll_run_id', 'employee_id'], 'payroll_inputs_run_employee_idx');
            $table->index(['company_id', 'payroll_run_id', 'source_type'], 'payroll_inputs_source_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_inputs');
        Schema::dropIfExists('payroll_adjustments');

        Schema::table('payroll_runs', function (Blueprint $table): void {
            $table->dropColumn(['input_summary', 'inputs_generated_at']);
        });
    }
};
