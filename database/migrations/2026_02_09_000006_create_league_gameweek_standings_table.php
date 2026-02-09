<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_gameweek_standings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('gameweek');
            $table->foreignId('manager_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('rank');
            $table->integer('points');
            $table->integer('total_points');
            $table->decimal('difference_to_average', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['league_id', 'gameweek', 'manager_id'], 'league_gw_standings_unique');
            $table->index(['league_id', 'gameweek']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_gameweek_standings');
    }
};
