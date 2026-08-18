<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Platform-level tracking management (super admin):
 *  - tracking_pixels: script snippets (Meta/FB pixel, GA4/gtag, TikTok,
 *    Google Ads, Hotjar, custom) injected site-wide by placement.
 *  - tracking_links: reusable campaign/conversion tracking URLs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_pixels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider'); // facebook|google|tiktok|gtag|hotjar|custom
            $table->string('placement')->default('head'); // head|body
            $table->text('code'); // the full <script> snippet
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        Schema::create('tracking_links', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_links');
        Schema::dropIfExists('tracking_pixels');
    }
};
