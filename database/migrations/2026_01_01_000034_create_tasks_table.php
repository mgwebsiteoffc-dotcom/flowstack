<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('todo'); // backlog|todo|in_progress|in_review|waiting_approval|done|blocked
            $table->string('priority')->default('medium'); // urgent|high|medium|low
            $table->string('service_type')->nullable(); // digital_marketing|shopify_operations|social_media|website_management|ai_automation|internal
            $table->string('task_type')->default('one_time'); // recurring|one_time|client_request|internal
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->date('due_date')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->nullable();
            $table->string('approval_status')->default('not_needed'); // not_needed|pending|approved|rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_type')->nullable(); // daily|weekly|biweekly|monthly|custom
            $table->integer('recurrence_interval')->default(1);
            $table->json('recurrence_days')->nullable();
            $table->date('next_recurrence_date')->nullable();
            $table->date('recurrence_ends_at')->nullable();
            $table->foreignId('parent_recurring_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->integer('order_index')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'assigned_to']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'due_date']);
            $table->index(['tenant_id', 'client_id']);
            $table->index(['is_recurring', 'next_recurrence_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
