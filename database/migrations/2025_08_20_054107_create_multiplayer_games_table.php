<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultiplayerGamesTable extends Migration
{
    public function up()
    {
        Schema::create('multiplayer_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_one_id')->constrained('users');
            $table->foreignId('player_two_id')->nullable()->constrained('users');
            $table->json('questions')->nullable();
            $table->integer('player_one_score')->default(0);
            $table->integer('player_two_score')->default(0);
            $table->unsignedBigInteger('current_turn')->nullable();
            $table->string('status')->default('pending');
            $table->string('game_end_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('multiplayer_games');
    }
}
