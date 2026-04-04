<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fpl_sync_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('event')->unique();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->json('meta')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fpl_sync_runs');
    }
};
