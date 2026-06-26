<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_widgets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('dashboard_key', 100);
            $table->string('widget_key', 100);
            $table->string('name');
            $table->string('widget_type', 32)->default('metric');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->foreignId('kpi_definition_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('report_dataset_id')->nullable()->constrained()->nullOnDelete();
            $table->json('configuration')->nullable();
            $table->unsignedInteger('freshness_expectation_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'dashboard_key', 'widget_key']);
            $table->index(['company_id', 'dashboard_key', 'is_active']);
        });

        Schema::create('dashboard_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('dashboard_key', 100);
            $table->string('scope_signature', 191);
            $table->string('source_signature', 191);
            $table->unsignedInteger('freshness_expectation_minutes')->default(60);
            $table->timestamp('generated_at');
            $table->timestamp('expires_at');
            $table->json('payload');
            $table->timestamps();

            $table->index(['company_id', 'dashboard_key', 'scope_signature', 'source_signature'], 'dashboard_snapshots_lookup_idx');
            $table->index(['company_id', 'dashboard_key', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_snapshots');
        Schema::dropIfExists('dashboard_widgets');
    }
};
