<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_policy_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->unsignedInteger('policy_version')->nullable();
            $table->decimal('available_days', 8, 2)->default(0);
            $table->decimal('booked_days', 8, 2)->default(0);
            $table->decimal('used_days', 8, 2)->default(0);
            $table->decimal('accrued_days', 8, 2)->default(0);
            $table->decimal('carry_forward_days', 8, 2)->default(0);
            $table->decimal('projected_encashable_days', 8, 2)->default(0);
            $table->date('current_period_start')->nullable();
            $table->date('current_period_end')->nullable();
            $table->string('last_calculation_hash', 64)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'employee_id', 'leave_type_id'], 'leave_balances_employee_type_unique');
            $table->index(['company_id', 'employee_id'], 'leave_balances_employee_idx');
            $table->index(['company_id', 'leave_type_id'], 'leave_balances_type_idx');
        });

        Schema::create('leave_balance_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_balance_id')->constrained('leave_balances')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_policy_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('entry_type', 50);
            $table->decimal('quantity_days', 8, 2);
            $table->decimal('balance_before_days', 8, 2)->default(0);
            $table->decimal('balance_after_days', 8, 2)->default(0);
            $table->date('effective_on');
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['company_id', 'leave_balance_id', 'entry_type', 'reference_type', 'reference_id'],
                'leave_balance_entries_ref_unique',
            );
            $table->index(['company_id', 'employee_id', 'effective_on'], 'leave_balance_entries_employee_date_idx');
            $table->index(['company_id', 'reference_type', 'reference_id'], 'leave_balance_entries_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balance_entries');
        Schema::dropIfExists('leave_balances');
    }
};
