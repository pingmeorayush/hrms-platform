<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('break_duration_minutes')->default(0);
            $table->unsignedSmallInteger('grace_minutes')->default(0);
            $table->unsignedInteger('working_hours_minutes');
            $table->boolean('is_overnight')->default(false);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('assignment_type', 30);
            $table->foreignId('employee_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('active');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'assignment_type', 'effective_from'], 'shift_assignments_company_type_from_idx');
            $table->index(['company_id', 'employee_id', 'effective_from'], 'shift_assignments_company_employee_idx');
            $table->index(['company_id', 'department_id', 'effective_from'], 'shift_assignments_company_department_idx');
            $table->index(['company_id', 'location_id', 'effective_from'], 'shift_assignments_company_location_idx');
        });

        Schema::create('shift_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('work_date');
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('scheduled');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'work_date']);
            $table->index(['company_id', 'work_date']);
            $table->index(['company_id', 'employee_id', 'work_date'], 'shift_rosters_company_employee_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_rosters');
        Schema::dropIfExists('shift_assignments');
        Schema::dropIfExists('shifts');
    }
};
