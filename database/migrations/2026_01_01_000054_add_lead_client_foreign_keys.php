<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * clients.lead_id and leads.converted_to_client_id reference each other,
     * so the FK constraints are added after both tables exist.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreign('lead_id')->references('id')->on('leads')->nullOnDelete();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreign('converted_to_client_id')->references('id')->on('clients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['converted_to_client_id']);
        });
    }
};
