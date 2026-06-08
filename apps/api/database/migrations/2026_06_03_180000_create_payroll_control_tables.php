<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('frequency', 30);
            $table->string('timezone', 100);
            $table->unsignedTinyInteger('payroll_day')->nullable();
            $table->unsignedTinyInteger('payroll_weekday')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('status', 30)->default('active');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'name'], 'payroll_calendars_company_name_uniq');
            $table->index(['company_id', 'frequency', 'status'], 'payroll_calendars_frequency_status_idx');
        });

        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('payroll_calendar_id')->constrained('payroll_calendars')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('frequency', 30);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('payroll_date');
            $table->string('status', 30)->default('draft');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['payroll_calendar_id', 'start_date', 'end_date'], 'payroll_periods_calendar_span_uniq');
            $table->index(['company_id', 'status', 'start_date', 'end_date'], 'payroll_periods_status_span_idx');
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('frequency', 30);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('blocked');
            $table->json('prerequisite_snapshot');
            $table->json('prerequisite_summary');
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['payroll_period_id'], 'payroll_runs_period_uniq');
            $table->index(['company_id', 'status', 'start_date', 'end_date'], 'payroll_runs_status_span_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('payroll_calendars');
    }
};
