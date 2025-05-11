<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('practice_records', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained()->cascadeOnDelete();
			$table->foreignId('file_id')->constrained()->cascadeOnDelete();
			$table->string('type'); // 'flashcard' or 'quiz'
			$table->integer('correct_answers');
			$table->integer('total_questions');
			$table->json('mistakes')->nullable(); // Store mistakes as JSON
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('practice_records');
	}
};
