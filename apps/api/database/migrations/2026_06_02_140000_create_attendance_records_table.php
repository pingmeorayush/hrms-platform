<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('shift_roster_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->date('attendance_date');
            $table->dateTime('check_in_at')->nullable();
            $table->string('check_in_channel', 30)->nullable();
            $table->string('check_in_ip_address', 45)->nullable();
            $table->text('check_in_user_agent')->nullable();
            $table->json('check_in_metadata')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->string('check_out_channel', 30)->nullable();
            $table->string('check_out_ip_address', 45)->nullable();
            $table->text('check_out_user_agent')->nullable();
            $table->json('check_out_metadata')->nullable();
            $table->unsignedInteger('worked_minutes')->nullable();
            $table->string('primary_status', 30)->nullable();
            $table->dateTime('scheduled_start_at')->nullable();
            $table->dateTime('scheduled_end_at')->nullable();
            $table->unsignedInteger('scheduled_work_minutes')->nullable();
            $table->unsignedInteger('break_duration_minutes')->default(0);
            $table->boolean('is_late')->default(false);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->boolean('is_half_day')->default(false);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->boolean('is_weekend')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->string('holiday_name', 150)->nullable();
            $table->boolean('is_early_departure')->default(false);
            $table->unsignedInteger('early_departure_minutes')->default(0);
            $table->dateTime('calculated_at')->nullable();
            $table->json('calculation_metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'attendance_date']);
            $table->index(['company_id', 'attendance_date']);
            $table->index(['company_id', 'employee_id', 'attendance_date'], 'attendance_records_company_employee_date_idx');
            $table->index(['employee_id', 'check_out_at'], 'attendance_records_employee_checkout_idx');
            $table->index(['company_id', 'primary_status'], 'attendance_records_company_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
