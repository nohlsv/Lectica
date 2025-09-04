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
            // Add collection support - multiplayer games can use either a single file or a collection
            $table->foreignId('collection_id')->nullable()->constrained()->onDelete('cascade')->after('file_id');

            // Make file_id nullable since games can now use collections instead
            $table->foreignId('file_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('multiplayer_games', function (Blueprint $table) {
            $table->dropForeign(['collection_id']);
            $table->dropColumn('collection_id');

            // Restore file_id as required
            $table->foreignId('file_id')->nullable(false)->change();
        });
    }
};
