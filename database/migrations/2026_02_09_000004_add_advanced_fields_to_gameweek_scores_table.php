<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gameweek_scores', function (Blueprint $table): void {
            $table->integer('total_points')->nullable()->after('points');
            $table->unsignedBigInteger('overall_rank')->nullable()->after('total_points');
            $table->integer('bank')->nullable()->after('overall_rank');
            $table->integer('value')->nullable()->after('bank');
            $table->integer('event_transfers')->default(0)->after('value');
            $table->integer('event_transfers_cost')->default(0)->after('event_transfers');
            $table->integer('points_on_bench')->default(0)->after('event_transfers_cost');
            $table->integer('autop_sub_points')->default(0)->after('points_on_bench');
            $table->integer('captain_points')->nullable()->after('autop_sub_points');
            $table->integer('vice_captain_points')->nullable()->after('captain_points');
            $table->integer('best_pick_points')->nullable()->after('vice_captain_points');

            $table->index('gameweek');
            $table->index('season_year');
        });
    }

    public function down(): void
    {
        Schema::table('gameweek_scores', function (Blueprint $table): void {
            $table->dropIndex(['gameweek']);
            $table->dropIndex(['season_year']);
            $table->dropColumn([
                'total_points',
                'overall_rank',
                'bank',
                'value',
                'event_transfers',
                'event_transfers_cost',
                'points_on_bench',
                'autop_sub_points',
                'captain_points',
                'vice_captain_points',
                'best_pick_points',
            ]);
        });
    }
};
