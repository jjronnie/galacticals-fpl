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
      Schema::create('gameweek_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('manager_id')->constrained()->onDelete('cascade');
            $table->integer('gameweek');
            $table->integer('points');
            $table->timestamps();
            
            $table->unique(['season_id', 'manager_id', 'gameweek']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gameweek_performances');
    }
};
