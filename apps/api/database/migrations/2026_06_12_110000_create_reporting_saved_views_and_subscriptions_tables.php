<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_report_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_dataset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('view_uuid')->unique();
            $table->string('name', 120);
            $table->string('description', 500)->nullable();
            $table->string('status', 32)->default('active');
            $table->string('share_scope', 32)->default('private');
            $table->json('shared_role_names')->nullable();
            $table->json('filters')->nullable();
            $table->json('filter_operators')->nullable();
            $table->string('sort_by', 64)->nullable();
            $table->string('sort_direction', 4)->nullable();
            $table->string('drilldown_path', 64)->nullable();
            $table->json('presentation_preferences')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'owner_user_id', 'status']);
            $table->index(['company_id', 'share_scope', 'status']);
        });

        Schema::create('report_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_dataset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('saved_report_view_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('subscription_uuid')->unique();
            $table->string('name', 120);
            $table->string('description', 500)->nullable();
            $table->string('status', 32)->default('active');
            $table->string('delivery_channel', 32);
            $table->string('delivery_target', 32);
            $table->string('export_format', 16);
            $table->string('frequency', 16);
            $table->string('timezone', 64);
            $table->json('schedule_config');
            $table->json('filters')->nullable();
            $table->json('filter_operators')->nullable();
            $table->string('sort_by', 64)->nullable();
            $table->string('sort_direction', 4)->nullable();
            $table->string('drilldown_path', 64)->nullable();
            $table->timestamp('next_delivery_at')->nullable();
            $table->timestamp('last_delivered_at')->nullable();
            $table->string('last_delivery_status', 32)->nullable();
            $table->text('last_delivery_error')->nullable();
            $table->foreignId('last_report_export_id')->nullable()->constrained('report_exports')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'owner_user_id', 'status']);
            $table->index(['company_id', 'next_delivery_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_subscriptions');
        Schema::dropIfExists('saved_report_views');
    }
};
