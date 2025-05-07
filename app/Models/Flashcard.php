<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Flashcard extends Model
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
        'answer',
    ];

    /**
     * Get the file that owns the flashcard.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
