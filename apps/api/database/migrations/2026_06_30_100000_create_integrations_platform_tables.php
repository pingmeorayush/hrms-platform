<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('system_key', 64);
            $table->string('version', 16)->default('v1');
            $table->string('name', 120);
            $table->string('direction', 16);
            $table->string('status', 32)->default('draft');
            $table->string('auth_mode', 32)->default('hmac_sha256');
            $table->string('endpoint_url')->nullable();
            $table->text('description')->nullable();
            $table->json('scopes')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'system_key']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('webhook_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('integration_connection_id')->constrained('integration_connections')->cascadeOnDelete();
            $table->uuid('subscription_key')->unique();
            $table->string('version', 16)->default('v1');
            $table->string('event_key', 128);
            $table->string('direction', 16);
            $table->string('status', 32)->default('active');
            $table->string('endpoint_url')->nullable();
            $table->text('secret');
            $table->json('custom_headers')->nullable();
            $table->json('filter_rules')->nullable();
            $table->timestamp('last_delivery_at')->nullable();
            $table->timestamp('last_received_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'event_key', 'direction']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('integration_sync_jobs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('integration_connection_id')->nullable()->constrained('integration_connections')->nullOnDelete();
            $table->foreignId('webhook_subscription_id')->nullable()->constrained('webhook_subscriptions')->nullOnDelete();
            $table->uuid('job_uuid')->unique();
            $table->string('version', 16)->default('v1');
            $table->string('system_key', 64);
            $table->string('event_key', 128);
            $table->string('direction', 16);
            $table->string('status', 32)->default('queued');
            $table->string('trigger_source', 32)->default('manual_event');
            $table->string('entity_type', 64)->nullable();
            $table->string('entity_id', 64)->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('request_headers')->nullable();
            $table->json('response_headers')->nullable();
            $table->unsignedInteger('attempts_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('retried_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('audit_metadata')->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status', 'queued_at']);
            $table->index(['company_id', 'event_key', 'created_at']);
            $table->index(['company_id', 'webhook_subscription_id', 'status']);
        });

        Schema::create('integration_sync_errors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('integration_sync_job_id')->constrained('integration_sync_jobs')->cascadeOnDelete();
            $table->unsignedSmallInteger('attempt_number');
            $table->string('error_code', 64)->nullable();
            $table->text('error_message');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('request_headers')->nullable();
            $table->json('response_headers')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'occurred_at']);
            $table->index(['integration_sync_job_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_sync_errors');
        Schema::dropIfExists('integration_sync_jobs');
        Schema::dropIfExists('webhook_subscriptions');
        Schema::dropIfExists('integration_connections');
    }
};
