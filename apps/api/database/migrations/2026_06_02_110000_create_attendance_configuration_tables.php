<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name', 150)->default('Default Attendance Policy');
            $table->unsignedInteger('working_hours_minutes')->default(480);
            $table->unsignedSmallInteger('grace_minutes')->default(15);
            $table->unsignedSmallInteger('late_after_minutes')->default(15);
            $table->unsignedInteger('half_day_minutes')->default(240);
            $table->boolean('overtime_eligible')->default(true);
            $table->unsignedInteger('overtime_after_minutes')->nullable();
            $table->json('weekend_rule');
            $table->boolean('work_from_home_allowed')->default(false);
            $table->boolean('enforce_geofence')->default(false);
            $table->unsignedInteger('allowed_radius_meters')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique('company_id');
        });

        Schema::create('holiday_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->foreignId('location_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->boolean('is_default')->default(false);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'is_default']);
            $table->index(['company_id', 'location_id', 'department_id'], 'holiday_calendars_scope_idx');
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('holiday_calendar_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name', 150);
            $table->date('holiday_date');
            $table->string('type', 30);
            $table->boolean('is_optional')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['holiday_calendar_id', 'holiday_date', 'name']);
            $table->index(['company_id', 'holiday_date']);
            $table->index(['holiday_calendar_id', 'holiday_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('holiday_calendars');
        Schema::dropIfExists('attendance_policies');
    }
};
