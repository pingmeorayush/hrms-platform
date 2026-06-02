<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'name']);
        });

        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'name']);
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->string('timezone')->default('UTC');
            $table->string('currency', 3)->default('USD');
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code', 30)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'name']);
        });

        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->unique(['company_id', 'name']);
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('employee_code', 50);
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->date('date_of_joining');
            $table->string('employment_type', 50);
            $table->string('employment_status', 50)->default('active');
            $table->foreignId('department_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('designation_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->text('termination_reason')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'employee_code']);
            $table->unique(['company_id', 'email']);
            $table->index(['company_id', 'employment_status']);
            $table->index(['company_id', 'manager_id']);
        });

        Schema::create('employment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('action', 50);
            $table->date('effective_date');
            $table->foreignId('previous_department_id')->nullable()->constrained('departments')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('previous_designation_id')->nullable()->constrained('designations')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('previous_manager_id')->nullable()->constrained('employees')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('previous_location_id')->nullable()->constrained('locations')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->cascadeOnUpdate()->nullOnDelete();
            $table->string('previous_employment_status', 50)->nullable();
            $table->string('employment_status', 50)->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'effective_date']);
            $table->index(['company_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_histories');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('cost_centers');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('designations');
        Schema::dropIfExists('departments');
    }
};
