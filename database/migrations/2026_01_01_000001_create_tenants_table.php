<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('logo')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->timestamp('plan_started_at')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('is_trial')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('max_users')->nullable();
            $table->integer('max_clients')->nullable();
            $table->json('settings')->nullable();
            $table->string('lead365_webhook_secret')->nullable();
            $table->text('bikribook_api_key')->nullable();
            $table->text('bikribook_api_secret')->nullable();
            $table->string('bikribook_company_id')->nullable();
            $table->string('bikribook_base_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
