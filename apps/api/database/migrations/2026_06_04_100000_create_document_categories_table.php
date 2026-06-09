<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('name', 100);
            $table->string('repository_scope', 50);
            $table->string('default_visibility_scope', 50);
            $table->unsignedInteger('retention_days')->nullable();
            $table->json('allowed_role_names')->nullable();
            $table->string('status', 20);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'repository_scope']);
            $table->index(['company_id', 'status']);
        });

        Schema::table('documents', function (Blueprint $table): void {
            $table->foreignId('document_category_id')
                ->nullable()
                ->after('company_id')
                ->constrained('document_categories')
                ->nullOnDelete();

            $table->index(['company_id', 'document_category_id']);
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('document_category_id');
        });

        Schema::dropIfExists('document_categories');
    }
};
