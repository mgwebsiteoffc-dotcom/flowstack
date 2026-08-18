<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('lead365_lead_id', 100)->nullable();
            $table->string('source_type')->default('manual'); // lead365|manual|meta_ads|form_submission
            $table->string('company_name')->nullable();
            $table->string('contact_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('lead_source')->nullable();
            $table->string('campaign_name')->nullable();
            $table->string('ad_name')->nullable();
            $table->string('form_name')->nullable();
            $table->json('services_interested')->nullable();
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->string('current_stage')->nullable();
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->string('status')->default('active'); // active|won|lost|archived
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('won_value', 12, 2)->nullable();
            $table->timestamp('won_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->unsignedBigInteger('converted_to_client_id')->nullable()->index();
            $table->date('expected_close_date')->nullable();
            $table->integer('probability')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('lead365_created_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'current_stage']);
            $table->index(['tenant_id', 'assigned_to']);
            $table->index('lead365_lead_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
