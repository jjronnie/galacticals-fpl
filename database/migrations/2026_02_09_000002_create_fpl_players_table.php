<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fpl_players', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->foreignId('team_id')->constrained('fpl_teams')->cascadeOnDelete();
            $table->string('first_name');
            $table->string('second_name');
            $table->string('web_name');
            $table->unsignedTinyInteger('element_type');
            $table->unsignedInteger('now_cost')->default(0);
            $table->unsignedInteger('total_points')->default(0);
            $table->decimal('selected_by_percent', 6, 2)->default(0);
            $table->decimal('form', 6, 2)->default(0);
            $table->unsignedBigInteger('region')->nullable();
            $table->timestamps();

            $table->index('team_id');
            $table->index('web_name');
            $table->index('total_points');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fpl_players');
    }
};
