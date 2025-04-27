<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
	/** @use HasFactory<\Database\Factories\FileFactory> */
	use HasFactory;
    protected $fillable = ['name', 'path', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // public function tags(): BelongsToMany
    // {
    //     return $this->belongsToMany(Tag::class);
    // }

    // public function flashcards(): HasMany
    // {
    //     return $this->hasMany(Flashcard::class);
    // }

}
