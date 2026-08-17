<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pending delayed automation executions (trigger_delay_hours > 0). Written by
 * AutomationService::processEvent, executed by the CheckAutomationDelays job.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_delays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('rule_id')->constrained('automation_rules')->cascadeOnDelete();
            $table->string('event');
            $table->string('model_class');
            $table->unsignedBigInteger('model_id');
            $table->json('context')->nullable();
            $table->timestamp('run_at');
            $table->timestamps();

            $table->index('run_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_delays');
    }
};
