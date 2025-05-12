<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        // $table->enum('user_role', ['student', 'faculty', 'admin'])->default('student');
        // $table->boolean('verified')->default(false);
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_role', ['student', 'faculty', 'admin'])->default('student');
        });
        Schema::table('files', function (Blueprint $table) {
            $table->boolean('verified')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_role');
        });
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('verified');
        });
    }
};
