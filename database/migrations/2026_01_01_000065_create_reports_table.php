<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('title');
            $table->string('report_type')->default('monthly'); // weekly|monthly|quarterly|custom
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status')->default('draft'); // draft|final|shared
            $table->json('data');
            $table->text('insights')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('next_priorities')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('shared_at')->nullable();
            $table->boolean('shared_with_client')->default(false);
            $table->timestamp('client_viewed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
