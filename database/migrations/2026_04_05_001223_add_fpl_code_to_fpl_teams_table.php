<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fpl_teams', function (Blueprint $table) {
            $table->unsignedInteger('fpl_code')->nullable()->index()->after('short_name');
        });
    }

    public function down(): void
    {
        Schema::table('fpl_teams', function (Blueprint $table) {
            $table->dropIndex(['fpl_code']);
            $table->dropColumn('fpl_code');
        });
    }
};
