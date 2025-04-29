<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
	/** @use HasFactory<\Database\Factories\FileFactory> */
	use HasFactory;
    protected $fillable = ['name', 'path', 'content', 'file_hash', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'file_tag', 'file_id', 'tag_id');
    }

    /**
     * Get the users who have starred this file.
     */
    public function starredBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'file_stars')
            ->withTimestamps();
    }

    // public function flashcards(): HasMany
    // {
    //     return $this->hasMany(Flashcard::class);
    // }

}
