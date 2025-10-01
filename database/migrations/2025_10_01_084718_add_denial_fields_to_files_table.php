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
        Schema::table('files', function (Blueprint $table) {
            $table->boolean('is_denied')->default(false);
            $table->text('denial_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('denied_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('user_notified_of_denial')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn([
                'is_denied',
                'denial_reason',
                'verified_at',
                'denied_at',
                'verified_by',
                'user_notified_of_denial'
            ]);
        });
    }
};
