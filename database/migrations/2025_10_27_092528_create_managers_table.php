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
       
        Schema::create('managers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('league_id')->constrained()->cascadeOnDelete();
    $table->unsignedBigInteger('entry_id'); // FPL manager entry ID
    $table->string('player_name');
    $table->string('team_name');
    $table->integer('rank')->nullable();
    $table->integer('total_points')->nullable();
    $table->timestamps();
});


      
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('managers');
    }
};
