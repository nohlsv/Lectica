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
        Schema::table('multiplayer_games', function (Blueprint $table) {
            // Add last activity tracking for disconnect detection
            $table->timestamp('last_activity')->nullable();
            
            // Add private game functionality
            $table->boolean('is_private')->default(false);
            $table->string('game_code', 8)->nullable()->unique();
            
            // Add index for game_code for faster lookups
            $table->index(['game_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('multiplayer_games', function (Blueprint $table) {
            $table->dropIndex(['game_code']);
            $table->dropColumn([
                'last_activity',
                'is_private',
                'game_code'
            ]);
        });
    }
};
