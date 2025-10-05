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
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->json('user_answer'); // Store the user's answer(s)
            $table->boolean('is_correct');
            $table->string('context_type'); // 'battle' or 'multiplayer'
            $table->unsignedBigInteger('context_id'); // battle_id or multiplayer_game_id
            $table->timestamp('answered_at');
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['user_id', 'context_type', 'context_id']);
            $table->index(['quiz_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
