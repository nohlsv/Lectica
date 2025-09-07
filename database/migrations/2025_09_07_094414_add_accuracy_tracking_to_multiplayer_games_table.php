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
            // Add accuracy tracking fields for PVP mode
            $table->decimal('player_one_accuracy', 5, 2)->default(0)->after('correct_answers_p1');
            $table->decimal('player_two_accuracy', 5, 2)->default(0)->after('correct_answers_p2');
            $table->integer('player_one_streak', false, true)->default(0)->after('player_one_accuracy');
            $table->integer('player_two_streak', false, true)->default(0)->after('player_two_accuracy');
            $table->integer('player_one_max_streak', false, true)->default(0)->after('player_one_streak');
            $table->integer('player_two_max_streak', false, true)->default(0)->after('player_two_streak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('multiplayer_games', function (Blueprint $table) {
            $table->dropColumn([
                'player_one_accuracy',
                'player_two_accuracy',
                'player_one_streak',
                'player_two_streak',
                'player_one_max_streak',
                'player_two_max_streak'
            ]);
        });
    }
};
