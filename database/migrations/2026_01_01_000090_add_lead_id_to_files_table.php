<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Files can be attached to leads (Lead detail -> Files tab). The files table
 * originally only carried client_id; lead_id is added here so lead files are
 * tenant-isolated like everything else.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('client_id')->constrained('leads')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_id');
        });
    }
};
