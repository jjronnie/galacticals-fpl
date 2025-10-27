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
       Schema::create('gameweek_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained()->onDelete('cascade');
            $table->integer('gameweek');
            $table->integer('season_year'); // e.g., 2025 for 25/26 season
         
            $table->integer('points');
            $table->timestamps();

            // Enforce uniqueness: a manager can only have one score per gameweek per season
            $table->unique(['manager_id', 'gameweek', 'season_year']);
        });

      

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gameweek_scores');
    }
};
