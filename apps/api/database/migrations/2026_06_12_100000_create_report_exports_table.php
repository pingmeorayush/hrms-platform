<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_exports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_dataset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('export_uuid')->unique();
            $table->string('status', 32);
            $table->string('format', 16);
            $table->string('execution_mode', 16);
            $table->string('delivery_target', 32);
            $table->json('requested_filters')->nullable();
            $table->json('requested_filter_operators')->nullable();
            $table->string('sort_by', 64)->nullable();
            $table->string('sort_direction', 4)->nullable();
            $table->string('drilldown_path', 64)->nullable();
            $table->unsignedInteger('estimated_row_count')->nullable();
            $table->unsignedInteger('exported_row_count')->nullable();
            $table->json('visibility_posture')->nullable();
            $table->json('freshness_snapshot')->nullable();
            $table->string('disk', 64)->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('checksum_sha256', 64)->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('retention_expires_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->text('last_error')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'requested_by_user_id', 'requested_at']);
            $table->index(['company_id', 'report_dataset_id', 'requested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
    }
};
