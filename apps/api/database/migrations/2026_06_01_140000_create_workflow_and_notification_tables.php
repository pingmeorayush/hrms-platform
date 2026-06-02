<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->string('module');
            $table->text('description')->nullable();
            $table->boolean('is_template')->default(false);
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('active_version_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'key']);
            $table->index(['company_id', 'module', 'status']);
        });

        Schema::create('workflow_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_definition_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('status')->default('draft');
            $table->json('definition')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['workflow_definition_id', 'version']);
            $table->index(['workflow_definition_id', 'status']);
        });

        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_version_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->unsignedInteger('sequence');
            $table->string('approver_type');
            $table->string('approver_value');
            $table->json('available_actions')->nullable();
            $table->unsignedInteger('sla_hours')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['workflow_version_id', 'key']);
            $table->unique(['workflow_version_id', 'sequence']);
        });

        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('workflow_definition_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('workflow_version_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('reference_type');
            $table->string('reference_id');
            $table->string('status')->default('created');
            $table->unsignedInteger('current_stage_sequence')->nullable();
            $table->foreignId('started_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'reference_type', 'reference_id']);
        });

        Schema::create('workflow_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('workflow_instance_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('workflow_stage_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('stage_key');
            $table->string('stage_name');
            $table->unsignedInteger('sequence');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('assigned_to_role')->nullable();
            $table->string('status')->default('open');
            $table->json('available_actions')->nullable();
            $table->string('decision')->nullable();
            $table->text('decision_comment')->nullable();
            $table->foreignId('acted_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('delegated_to_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('acted_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'assigned_to_user_id', 'status']);
            $table->index(['workflow_instance_id', 'status']);
        });

        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->string('category');
            $table->string('channel');
            $table->string('subject')->nullable();
            $table->text('content');
            $table->json('variables')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['company_id', 'key', 'channel']);
            $table->index(['category', 'channel', 'status']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('notification_template_id')->nullable()->constrained('notification_templates')->cascadeOnUpdate()->nullOnDelete();
            $table->string('type')->default('system');
            $table->string('channel')->default('in_app');
            $table->string('title');
            $table->text('message');
            $table->string('priority')->default('normal');
            $table->string('status')->default('unread');
            $table->string('delivery_status')->default('delivered');
            $table->string('deep_link')->nullable();
            $table->unsignedSmallInteger('retry_count')->default(0);
            $table->text('last_error')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'user_id', 'status']);
            $table->index(['company_id', 'delivery_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('workflow_tasks');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_stages');
        Schema::dropIfExists('workflow_versions');
        Schema::dropIfExists('workflow_definitions');
    }
};
