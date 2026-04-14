<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fpl_fixtures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fpl_fixture_id')->unique();
            $table->unsignedInteger('event')->nullable()->index();
            $table->unsignedInteger('team_h');
            $table->unsignedInteger('team_a');
            $table->unsignedInteger('team_h_difficulty')->nullable();
            $table->unsignedInteger('team_a_difficulty')->nullable();
            $table->dateTime('kickoff_time')->nullable()->index();
            $table->boolean('started')->default(false);
            $table->boolean('finished')->default(false);
            $table->boolean('finished_provisional')->default(false);
            $table->integer('team_h_score')->nullable();
            $table->integer('team_a_score')->nullable();
            $table->unsignedInteger('minutes')->nullable();
            $table->unsignedBigInteger('pulse_id')->nullable();
            $table->json('stats')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fpl_fixtures');
    }
};
