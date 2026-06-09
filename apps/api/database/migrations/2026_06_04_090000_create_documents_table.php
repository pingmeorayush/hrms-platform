<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->string('repository_scope', 50);
            $table->string('linked_entity_type', 100)->nullable();
            $table->unsignedBigInteger('linked_entity_id')->nullable();
            $table->string('visibility_scope', 50);
            $table->string('original_file_name', 255);
            $table->string('disk', 50);
            $table->string('file_path', 500);
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size_bytes');
            $table->char('checksum_sha256', 64);
            $table->date('retention_until')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'repository_scope']);
            $table->index(['company_id', 'visibility_scope']);
            $table->index(['company_id', 'linked_entity_type', 'linked_entity_id'], 'documents_linked_entity_idx');
            $table->index(['company_id', 'retention_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
