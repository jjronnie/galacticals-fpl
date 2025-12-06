<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('managers', function (Blueprint $table) {
            $table->index(['league_id', 'entry_id']);
            $table->index('league_id');
        });

        Schema::table('gameweek_scores', function (Blueprint $table) {
            $table->index(['manager_id', 'gameweek', 'season_year']);
            $table->index('manager_id');
        });

        Schema::table('leagues', function (Blueprint $table) {
            $table->index('sync_status');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::table('managers', function (Blueprint $table) {
            $table->dropIndex(['league_id', 'entry_id']);
            $table->dropIndex(['league_id']);
        });

        Schema::table('gameweek_scores', function (Blueprint $table) {
            $table->dropIndex(['manager_id', 'gameweek', 'season_year']);
            $table->dropIndex(['manager_id']);
        });

        Schema::table('leagues', function (Blueprint $table) {
            $table->dropIndex(['sync_status']);
            $table->dropIndex(['user_id']);
        });
    }
};