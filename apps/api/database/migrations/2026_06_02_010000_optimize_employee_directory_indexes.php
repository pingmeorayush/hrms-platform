<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->index(['company_id', 'department_id'], 'employees_company_department_idx');
            $table->index(['company_id', 'designation_id'], 'employees_company_designation_idx');
            $table->index(['company_id', 'first_name', 'last_name'], 'employees_company_name_idx');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropIndex('employees_company_department_idx');
            $table->dropIndex('employees_company_designation_idx');
            $table->dropIndex('employees_company_name_idx');
        });
    }
};
