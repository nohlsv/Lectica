<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PracticeRecord extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id',
		'file_id',
		'type', // 'flashcard' or 'quiz'
		'correct_answers',
		'total_questions',
		'mistakes', // JSON field to store mistakes
	];

	protected $casts = [
		'mistakes' => 'json',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function file()
	{
		return $this->belongsTo(File::class);
	}
}
