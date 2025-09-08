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
            // Add file and monster relationships
            $table->foreignId('file_id')->nullable()->constrained('files')->after('player_two_id');
            $table->string('monster_id')->nullable()->after('file_id');

            // Add HP tracking for players and monster
            $table->integer('player_one_hp')->default(100)->after('monster_id');
            $table->integer('player_two_hp')->default(100)->after('player_one_hp');
            $table->integer('monster_hp')->default(100)->after('player_two_hp');

            // Add accuracy tracking
            $table->integer('correct_answers_p1')->default(0)->after('player_two_score');
            $table->integer('correct_answers_p2')->default(0)->after('correct_answers_p1');
            $table->integer('total_questions_p1')->default(0)->after('correct_answers_p2');
            $table->integer('total_questions_p2')->default(0)->after('total_questions_p1');

            // Remove the questions JSON field as we'll use the file's quizzes
            $table->dropColumn('questions');

            // Update status to use enum values
            $table->string('status')->default('waiting')->change();

            // Remove game_end_reason as we'll use status
            $table->dropColumn('game_end_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('multiplayer_games', function (Blueprint $table) {
            // Remove the new columns
            $table->dropForeign(['file_id']);
            $table->dropColumn([
                'file_id',
                'monster_id',
                'player_one_hp',
                'player_two_hp',
                'monster_hp',
                'correct_answers_p1',
                'correct_answers_p2',
                'total_questions_p1',
                'total_questions_p2'
            ]);

            // Add back the old columns
            $table->json('questions')->nullable();
            $table->string('game_end_reason')->nullable();
            $table->string('status')->default('pending')->change();
        });
    }
};
