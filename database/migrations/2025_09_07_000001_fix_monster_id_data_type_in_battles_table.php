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
        Schema::table('battles', function (Blueprint $table) {
            // Check if monster_id column exists before dropping it
            if (Schema::hasColumn('battles', 'monster_id')) {
                $table->dropColumn('monster_id');
            }
        });

        Schema::table('battles', function (Blueprint $table) {
            // Add the new integer foreign key monster_id column as nullable to avoid SQLite issues
            $table->foreignId('monster_id')->nullable()->constrained('monsters')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battles', function (Blueprint $table) {
            // Drop the foreign key constraint and column
            $table->dropForeign(['monster_id']);
            $table->dropColumn('monster_id');
        });

        Schema::table('battles', function (Blueprint $table) {
            // Restore the original string monster_id column
            $table->string('monster_id')->after('collection_id');
        });
    }
};
