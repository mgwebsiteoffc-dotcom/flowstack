<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OAuth tokens for third-party integrations (Google Calendar, etc.).
 * The token payload is encrypted at the application layer (Crypt) - the
 * column stores an encrypted JSON string, never raw credentials.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('provider'); // google_calendar
            $table->text('token_json'); // Crypt::encryptString(json_encode(tokens))
            $table->string('email')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'provider']);
        });

        // Calendar event + Meet link references on tasks.
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('calendar_event_id')->nullable()->after('order_index');
            $table->string('meet_link')->nullable()->after('calendar_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['calendar_event_id', 'meet_link']);
        });

        Schema::dropIfExists('integration_tokens');
    }
};
