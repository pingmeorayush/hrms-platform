<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->string('category', 30);
            $table->text('description')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->boolean('allows_half_day')->default(true);
            $table->string('color_token', 10)->default('#0972D3');
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'name']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'category']);
        });

        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('scope_key', 64);
            $table->decimal('annual_allowance_days', 8, 2)->default(0);
            $table->decimal('opening_balance_days', 8, 2)->default(0);
            $table->string('accrual_frequency', 30)->default('none');
            $table->decimal('carry_forward_limit_days', 8, 2)->default(0);
            $table->decimal('encashment_limit_days', 8, 2)->default(0);
            $table->decimal('max_consecutive_days', 8, 2)->default(1);
            $table->unsignedInteger('min_notice_days')->default(0);
            $table->unsignedInteger('requires_documentation_after_days')->nullable();
            $table->foreignId('applicable_department_id')->nullable()->constrained('departments')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('applicable_location_id')->nullable()->constrained('locations')->cascadeOnUpdate()->nullOnDelete();
            $table->json('eligibility_rule');
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'leave_type_id', 'scope_key'], 'leave_policies_scope_unique');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'leave_type_id']);
            $table->index(['company_id', 'applicable_department_id'], 'leave_policies_department_idx');
            $table->index(['company_id', 'applicable_location_id'], 'leave_policies_location_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_policies');
        Schema::dropIfExists('leave_types');
    }
};
