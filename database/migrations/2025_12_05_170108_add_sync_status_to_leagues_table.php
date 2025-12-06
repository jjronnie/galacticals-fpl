<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->string('sync_status')->default('completed')->after('season'); // completed, processing, failed
            $table->string('sync_message')->nullable()->after('sync_status');
            $table->integer('total_managers')->default(0)->after('sync_message');
            $table->integer('synced_managers')->default(0)->after('total_managers');
            $table->timestamp('last_synced_at')->nullable()->after('synced_managers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn(['sync_status', 'sync_message', 'total_managers', 'synced_managers', 'last_synced_at']);
        });
    }
};
