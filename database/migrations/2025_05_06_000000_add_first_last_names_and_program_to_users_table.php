<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
        });

        if (Schema::hasColumn('users', 'name')) {
            // Copy data from name to first_name
            DB::statement('UPDATE users SET first_name = name');

            // Now drop the name column
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back the name column
            $table->string('name')->nullable();

            // Copy data from first_name to name (we'll lose the last name info in rollback)
            DB::statement('UPDATE users SET name = first_name');

            // Drop the new columns
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
