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
        Schema::create('battles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('monster_id'); // Changed to string to match Monster class
            $table->foreignId('file_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'won', 'lost', 'abandoned']); // Updated to match BattleStatus enum
            $table->integer('player_hp');
            $table->integer('monster_hp');
            $table->integer('correct_answers')->default(0);
            $table->integer('total_questions')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battles');
        //
    }
};
