<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('managers', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('league_id')->constrained()->nullOnDelete();
            $table->string('player_first_name')->nullable()->after('player_name');
            $table->string('player_last_name')->nullable()->after('player_first_name');
            $table->string('region_name')->nullable()->after('player_last_name');
            $table->foreignId('favourite_team_id')->nullable()->after('region_name')->constrained('fpl_teams')->nullOnDelete();
            $table->timestamp('claimed_at')->nullable()->after('favourite_team_id');
            $table->timestamp('suspended_at')->nullable()->after('claimed_at');
            $table->text('notes')->nullable()->after('suspended_at');
            $table->string('sync_status')->default('completed')->after('notes');
            $table->string('sync_message')->nullable()->after('sync_status');
            $table->timestamp('last_synced_at')->nullable()->after('sync_message');

            $table->index('entry_id', 'managers_entry_id_index');
            $table->index('user_id', 'managers_user_id_index');
            $table->index('suspended_at', 'managers_suspended_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('managers', function (Blueprint $table): void {
            $table->dropIndex('managers_entry_id_index');
            $table->dropIndex('managers_user_id_index');
            $table->dropIndex('managers_suspended_at_index');

            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('favourite_team_id');
            $table->dropColumn([
                'player_first_name',
                'player_last_name',
                'region_name',
                'claimed_at',
                'suspended_at',
                'notes',
                'sync_status',
                'sync_message',
                'last_synced_at',
            ]);
        });
    }
};
