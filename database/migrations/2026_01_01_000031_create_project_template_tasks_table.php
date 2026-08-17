<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_template_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('project_templates')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('task_type')->default('one_time'); // one_time|recurring
            $table->string('recurrence_type')->nullable(); // daily|weekly|biweekly|monthly|custom
            $table->string('default_priority')->default('medium'); // urgent|high|medium|low
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_template_tasks');
    }
};
