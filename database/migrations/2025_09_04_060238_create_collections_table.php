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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_original')->default(true); // true if user created it, false if copied
            $table->foreignId('original_collection_id')->nullable()->constrained('collections')->onDelete('set null');
            $table->foreignId('original_creator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('cover_image')->nullable();
            $table->json('tags')->nullable();
            $table->integer('file_count')->default(0);
            $table->integer('total_questions')->default(0);
            $table->integer('copy_count')->default(0); // how many times this collection was copied
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'is_public']);
            $table->index(['is_public', 'created_at']);
            $table->index('original_collection_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
