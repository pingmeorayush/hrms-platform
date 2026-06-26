<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_definitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('key', 64);
            $table->string('name');
            $table->string('domain', 32);
            $table->text('description')->nullable();
            $table->text('formula');
            $table->json('source_references');
            $table->string('grain', 64);
            $table->string('certification_status', 32)->default('draft');
            $table->text('review_notes')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('certified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('certified_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'key']);
            $table->index(['company_id', 'domain']);
            $table->index(['company_id', 'certification_status']);
        });

        Schema::create('report_datasets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('key', 64);
            $table->string('name');
            $table->string('domain', 32);
            $table->text('description')->nullable();
            $table->json('source_references');
            $table->string('grain', 64);
            $table->json('approved_fields');
            $table->json('approved_filters')->nullable();
            $table->json('drilldown_paths')->nullable();
            $table->json('masking_posture');
            $table->unsignedInteger('freshness_expectation_minutes')->nullable();
            $table->string('certification_status', 32)->default('draft');
            $table->text('review_notes')->nullable();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('certified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('certified_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'key']);
            $table->index(['company_id', 'domain']);
            $table->index(['company_id', 'certification_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_datasets');
        Schema::dropIfExists('kpi_definitions');
    }
};
