<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_acknowledgements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('policy_title', 150);
            $table->string('policy_version', 50)->nullable();
            $table->string('status', 32)->default('assigned');
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users');
            $table->date('due_date')->nullable();
            $table->text('assignment_notes')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by_user_id')->nullable()->constrained('users');
            $table->text('acknowledgement_notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'document_id', 'employee_id'], 'policy_acknowledgements_document_employee_unique');
            $table->index(['employee_id', 'status', 'due_date'], 'policy_acknowledgements_employee_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_acknowledgements');
    }
};
