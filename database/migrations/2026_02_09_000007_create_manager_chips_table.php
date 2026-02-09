<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manager_chips', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manager_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('gameweek');
            $table->string('chip_name');
            $table->integer('points_before')->nullable();
            $table->integer('points_after')->nullable();
            $table->timestamps();

            $table->unique(['manager_id', 'gameweek', 'chip_name'], 'manager_chips_unique_manager_gw_chip');
            $table->index(['chip_name', 'gameweek']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_chips');
    }
};
