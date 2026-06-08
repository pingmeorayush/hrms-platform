<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_compensations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_structure_id')->constrained()->restrictOnDelete();
            $table->foreignId('previous_revision_id')->nullable()->constrained('employee_compensations')->nullOnDelete();
            $table->string('salary_structure_code', 40);
            $table->unsignedInteger('salary_structure_version');
            $table->string('currency', 10);
            $table->string('pay_frequency', 20);
            $table->decimal('annual_ctc_amount', 12, 2);
            $table->decimal('basic_salary_amount', 12, 2);
            $table->decimal('gross_salary_amount', 12, 2);
            $table->decimal('net_salary_amount', 12, 2);
            $table->string('revision_reason', 40);
            $table->date('effective_from');
            $table->date('revision_date');
            $table->text('notes')->nullable();
            $table->json('component_snapshot');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'employee_id', 'effective_from'], 'employee_compensations_effective_unique');
            $table->index(['company_id', 'employee_id', 'revision_date'], 'employee_compensations_employee_revision_idx');
            $table->index(['company_id', 'salary_structure_id'], 'employee_compensations_structure_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_compensations');
    }
};
