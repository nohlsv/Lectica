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
            // Add game mode field to distinguish between PVP and PVE
            $table->string('game_mode')->default('pve')->after('status'); // 'pve' or 'pvp'

            // Make monster_id nullable for PVP mode (no monster in PVP)
            $table->string('monster_id')->nullable()->change();

            // Make monster_hp nullable for PVP mode
            $table->integer('monster_hp')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('multiplayer_games', function (Blueprint $table) {
            $table->dropColumn('game_mode');

            // Restore monster_id and monster_hp to non-nullable (if needed)
            $table->string('monster_id')->nullable(false)->change();
            $table->integer('monster_hp')->default(100)->change();
        });
    }
};
