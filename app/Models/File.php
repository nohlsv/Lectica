<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class File extends Model
{
    /** @use HasFactory<\Database\Factories\FileFactory> */
    use HasFactory;
    protected $fillable = ['name', 'description', 'path', 'content', 'file_hash', 'user_id'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['is_starred', 'can_edit', 'flashcards_count', 'quizzes_count'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('withStarCount', function ($builder) {
            $builder->withCount('starredBy as star_count');
        });
    }

    /**
     * Get the is_starred attribute.
     *
     * @return bool
     */
    public function getIsStarredAttribute(): bool
    {
        $user = Auth::user();
        return $user ? $user->hasStarred($this) : false;
    }

    /**
     * Get the can_edit attribute.
     *
     * @return bool
     */
    public function getCanEditAttribute(): bool
    {
        $user = Auth::user();
        return $user ? ($user->id === $this->user_id || $user->isAdmin()) : false;
    }

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

    /**
     * Get the flashcards for the file.
     */
    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    /**
     * Get the quizzes for the file.
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * Get the flashcards_count attribute.
     *
     * @return int
     */
    public function getFlashcardsCountAttribute(): int
    {
        return $this->flashcards()->count();
    }

    /**
     * Get the quizzes_count attribute.
     *
     * @return int
     */
    public function getQuizzesCountAttribute(): int
    {
        return $this->quizzes()->count();
    }

    /**
     * Check if the file has any quizzes.
     *
     * @return bool
     */
    public function hasQuizzes(): bool
    {
        return $this->quizzes()->exists();
    }

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Get the collections that contain this file.
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_file')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('collection_file.order');
    }

    /**
     * Get the battles that use this file.
     */
    public function battles(): HasMany
    {
        return $this->hasMany(Battle::class);
    }

    /**
     * Get the multiplayer games that use this file.
     */
    public function multiplayerGames(): HasMany
    {
        return $this->hasMany(MultiplayerGame::class);
    }

    /**
     * Check if this file is in a specific collection.
     */
    public function isInCollection(Collection $collection): bool
    {
        return $this->collections()->where('collection_id', $collection->id)->exists();
    }
}
