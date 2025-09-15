<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'is_original',
        'original_collection_id',
        'original_creator_id',
        'cover_image',
        'tags',
        'file_count',
        'total_questions',
        'copy_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_original' => 'boolean',
        'tags' => 'array',
        'file_count' => 'integer',
        'total_questions' => 'integer',
        'copy_count' => 'integer',
    ];

    protected $appends = ['is_favorited', 'can_edit', 'can_copy'];

    /**
     * Get the user who owns the collection.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the original collection if this is a copy.
     */
    public function originalCollection(): BelongsTo
    {
        return $this->belongsTo(Collection::class, 'original_collection_id');
    }

    /**
     * Get the original creator if this is a copy.
     */
    public function originalCreator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_creator_id');
    }

    /**
     * Get all copies of this collection.
     */
    public function copies(): HasMany
    {
        return $this->hasMany(Collection::class, 'original_collection_id');
    }

    /**
     * Get the files in this collection with ordering.
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'collection_file')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('collection_file.order');
    }

    /**
     * Get users who favorited this collection.
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'collection_favorites')
            ->withTimestamps();
    }

    /**
     * Get battles using this collection.
     */
    public function battles(): HasMany
    {
        return $this->hasMany(Battle::class);
    }

    /**
     * Get multiplayer games using this collection.
     */
    public function multiplayerGames(): HasMany
    {
        return $this->hasMany(MultiplayerGame::class);
    }

    /**
     * Check if current user has favorited this collection.
     */
    public function getIsFavoritedAttribute(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return $this->favoritedBy()->where('user_id', Auth::id())->exists();
    }

    /**
     * Check if current user can edit this collection.
     */
    public function getCanEditAttribute(): bool
    {
        if (!Auth::check()) {
            return false;
        }
        // Only allow editing if user owns and it's original
        return $this->user_id === Auth::id() && $this->is_original;
    }

    /**
     * Check if current user can copy this collection.
     */
    public function getCanCopyAttribute(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        // Can't copy your own collection or private collections from others
        return $this->user_id !== Auth::id() && $this->is_public;
    }

    /**
     * Get the total number of quizzes in this collection.
     */
    public function getTotalQuizzesCount(): int
    {
        return $this->files()
            ->withCount('quizzes')
            ->get()
            ->sum('quizzes_count');
    }

    /**
     * Update file count and total questions for this collection.
     */
    public function updateCounts(): void
    {
        $files = $this->files()->with('quizzes')->get();

        $fileCount = $files->count();
        $totalQuestions = $files->sum(function ($file) {
            return $file->quizzes->count();
        });

        $this->update([
            'file_count' => $fileCount,
            'total_questions' => $totalQuestions,
        ]);
    }

    /**
     * Add a file to this collection.
     */
    public function addFile(File $file, int $order = null): void
    {
        if ($order === null) {
            $order = $this->files()->count();
        }

        $this->files()->attach($file->id, ['order' => $order]);
        $this->updateCounts();
    }

    /**
     * Remove a file from this collection.
     */
    public function removeFile(File $file): void
    {
        $this->files()->detach($file->id);
        $this->updateCounts();
        $this->reorderFiles();
    }

    /**
     * Reorder files in the collection to maintain sequential ordering.
     */
    public function reorderFiles(): void
    {
        $files = $this->files()->orderBy('collection_file.order')->get();

        foreach ($files as $index => $file) {
            $this->files()->updateExistingPivot($file->id, ['order' => $index]);
        }
    }

    /**
     * Create a copy of this collection for the given user.
     */
    public function createCopy(User $user, string $newName = null): Collection
    {
        $copy = Collection::create([
            'user_id' => $user->id,
            'name' => $newName ?? "Copy of {$this->name}",
            'description' => $this->description,
            'is_public' => false, // Copies start as private
            'is_original' => false,
            'original_collection_id' => $this->is_original ? $this->id : $this->original_collection_id,
            'original_creator_id' => $this->is_original ? $this->user_id : $this->original_creator_id,
            'tags' => $this->tags,
        ]);

        // Copy all files with their order
        $files = $this->files()->withPivot('order')->get();
        foreach ($files as $file) {
            $copy->files()->attach($file->id, ['order' => $file->pivot->order]);
        }

        $copy->updateCounts();

        // Increment copy count on original
        $originalCollection = $this->is_original ? $this : $this->originalCollection;
        if ($originalCollection) {
            $originalCollection->increment('copy_count');
        }

        return $copy;
    }

    /**
     * Toggle favorite status for the current user.
     */
    public function toggleFavorite(User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$user) {
            return false;
        }

        if ($this->favoritedBy()->where('user_id', $user->id)->exists()) {
            $this->favoritedBy()->detach($user->id);
            return false;
        } else {
            $this->favoritedBy()->attach($user->id);
            return true;
        }
    }

    /**
     * Scope to get public collections.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to get original collections (not copies).
     */
    public function scopeOriginal(Builder $query): Builder
    {
        return $query->where('is_original', true);
    }

    /**
     * Scope to get collections owned by the current user.
     */
    public function scopeOwned(Builder $query): Builder
    {
        return $query->where('user_id', Auth::id());
    }

    /**
     * Scope to get collections favorited by the current user.
     */
    public function scopeFavorited(Builder $query): Builder
    {
        return $query->whereHas('favoritedBy', function ($q) {
            $q->where('user_id', Auth::id());
        });
    }

    /**
     * Scope to search collections by name or description.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter collections by tags.
     */
    public function scopeWithTags(Builder $query, array $tags): Builder
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Get collections that are accessible to the current user.
     */
    public function scopeAccessible(Builder $query): Builder
    {
        $userId = Auth::id();

        return $query->where(function ($q) use ($userId) {
            $q->where('is_public', true)
              ->orWhere('user_id', $userId);
        });
    }
}
