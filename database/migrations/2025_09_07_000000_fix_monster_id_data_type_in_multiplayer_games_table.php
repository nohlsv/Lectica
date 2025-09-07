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
            // Drop the existing string monster_id column
            $table->dropColumn('monster_id');
        });

        Schema::table('multiplayer_games', function (Blueprint $table) {
            // Add the new integer foreign key monster_id column
            $table->foreignId('monster_id')->nullable()->constrained('monsters')->after('file_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('multiplayer_games', function (Blueprint $table) {
            // Drop the foreign key constraint and column
            $table->dropForeign(['monster_id']);
            $table->dropColumn('monster_id');
        });

        Schema::table('multiplayer_games', function (Blueprint $table) {
            // Restore the original string monster_id column
            $table->string('monster_id')->nullable()->after('file_id');
        });
    }
};
