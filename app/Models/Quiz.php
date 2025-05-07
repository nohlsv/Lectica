<?php

namespace App\Models;

use App\Enums\QuizType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quiz extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'file_id',
        'question',
        'type',
        'options',
        'answers',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'json',
        'answers' => 'json',
        'type' => QuizType::class,
    ];

    /**
     * Get the file that owns the quiz.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
