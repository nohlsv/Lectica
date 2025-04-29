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
        Schema::table('users', function (Blueprint $table) {
            $table->string('year_of_study')->nullable();

            // Drop the string program column if it exists
            if (Schema::hasColumn('users', 'program')) {
                $table->dropColumn('program');
            }

            // Add foreign key for program
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('year_of_study');
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
            $table->string('program')->nullable();
        });
    }
};
