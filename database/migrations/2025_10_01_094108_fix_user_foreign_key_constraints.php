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
            // Drop existing foreign key constraints
            $table->dropForeign(['player_one_id']);
            $table->dropForeign(['player_two_id']);
            $table->dropForeign(['winner_id']);
            
            // Recreate them with proper onDelete behavior
            $table->foreign('player_one_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('player_two_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('winner_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('multiplayer_games', function (Blueprint $table) {
            // Drop the new constraints
            $table->dropForeign(['player_one_id']);
            $table->dropForeign(['player_two_id']);
            $table->dropForeign(['winner_id']);
            
            // Recreate original constraints (without onDelete)
            $table->foreign('player_one_id')->references('id')->on('users');
            $table->foreign('player_two_id')->references('id')->on('users');
            $table->foreign('winner_id')->references('id')->on('users');
        });
    }
};
