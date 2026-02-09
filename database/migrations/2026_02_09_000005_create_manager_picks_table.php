<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manager_picks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manager_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('gameweek');
            $table->foreignId('player_id')->constrained('fpl_players')->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->unsignedTinyInteger('multiplier')->default(1);
            $table->boolean('is_captain')->default(false);
            $table->boolean('is_vice_captain')->default(false);
            $table->integer('event_points')->nullable();
            $table->timestamps();

            $table->unique(['manager_id', 'gameweek', 'player_id'], 'manager_picks_unique_manager_gw_player');
            $table->index(['manager_id', 'gameweek']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_picks');
    }
};
