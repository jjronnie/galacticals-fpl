<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('managers', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('claimed_at');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->index('verified_at', 'managers_verified_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('managers', function (Blueprint $table) {
            $table->dropIndex('managers_verified_at_index');
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn('verified_at');
        });
    }
};
