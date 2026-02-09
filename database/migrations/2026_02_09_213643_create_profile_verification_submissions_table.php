<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_verification_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('entry_id');
            $table->string('team_name');
            $table->string('player_name');
            $table->string('screenshot_path')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'profile_verifications_status_created_index');
            $table->index(['user_id', 'entry_id', 'created_at'], 'profile_verifications_user_entry_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_verification_submissions');
    }
};
