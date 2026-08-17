<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('industry')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('gstin')->nullable();
            $table->string('status')->default('active'); // active|inactive|onboarding|offboarding
            $table->string('health_score')->default('green'); // green|yellow|red
            $table->text('health_score_reason')->nullable();
            $table->decimal('monthly_retainer', 12, 2)->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->foreignId('account_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->string('bikribook_customer_id')->nullable();
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->boolean('portal_access_enabled')->default(false);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'account_manager_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
