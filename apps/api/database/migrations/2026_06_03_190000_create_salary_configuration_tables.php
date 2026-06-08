<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name', 100);
            $table->string('category', 30);
            $table->string('calculation_type', 30);
            $table->decimal('flat_amount', 12, 2)->nullable();
            $table->decimal('percentage_value', 8, 4)->nullable();
            $table->json('percentage_basis_component_codes')->nullable();
            $table->text('expression_formula')->nullable();
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_proratable')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status', 30)->default('active');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'code'], 'salary_components_company_code_uniq');
            $table->index(['company_id', 'category', 'status'], 'salary_components_category_status_idx');
        });

        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('previous_version_id')->nullable()->constrained('salary_structures')->cascadeOnUpdate()->nullOnDelete();
            $table->string('code', 40);
            $table->string('name', 120);
            $table->string('currency', 10);
            $table->string('country_code', 2);
            $table->string('pay_frequency', 30);
            $table->string('grade', 30)->nullable();
            $table->string('band', 30)->nullable();
            $table->string('level', 30)->nullable();
            $table->decimal('annual_ctc_amount', 14, 2);
            $table->decimal('basic_salary_amount', 14, 2);
            $table->decimal('gross_salary_amount', 14, 2);
            $table->decimal('net_salary_amount', 14, 2);
            $table->date('effective_from');
            $table->date('revision_date');
            $table->unsignedInteger('version')->default(1);
            $table->string('status', 30)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'code', 'version'], 'salary_structures_company_code_version_uniq');
            $table->index(['company_id', 'code', 'status'], 'salary_structures_code_status_idx');
        });

        Schema::create('salary_structure_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('salary_structure_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('display_order')->default(0);
            $table->decimal('configured_amount', 12, 2)->nullable();
            $table->decimal('configured_percentage', 8, 4)->nullable();
            $table->json('configured_basis_component_codes')->nullable();
            $table->text('configured_expression_formula')->nullable();
            $table->timestamps();

            $table->unique(['salary_structure_id', 'salary_component_id'], 'salary_structure_components_unique_component_idx');
            $table->index(['company_id', 'salary_structure_id', 'display_order'], 'salary_structure_components_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_structure_components');
        Schema::dropIfExists('salary_structures');
        Schema::dropIfExists('salary_components');
    }
};
