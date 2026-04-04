<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fpl_players', function (Blueprint $table) {
            $table->string('fpl_photo')->nullable()->index()->after('form');
        });
    }

    public function down(): void
    {
        Schema::table('fpl_players', function (Blueprint $table) {
            $table->dropIndex(['fpl_photo']);
            $table->dropColumn('fpl_photo');
        });
    }
};
