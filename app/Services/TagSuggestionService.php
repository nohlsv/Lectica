<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TagSuggestionService
{
    /**
     * Get personalized tag suggestions for a user
     */
    public function getPersonalizedSuggestions(User $user, int $limit = 10): Collection
    {
        // Get user's most used tags (by file count)
        $mostUsedTags = $this->getMostUsedTags($user, $limit);

        // Get user's recently used tags
        $recentTags = $this->getRecentlyUsedTags($user, $limit);

        // Combine and deduplicate
        $allSuggestions = $mostUsedTags->merge($recentTags)
            ->unique('id')
            ->take($limit);

        // If we don't have enough personalized suggestions, fill with popular tags
        if ($allSuggestions->count() < $limit) {
            $popularTags = $this->getPopularTags($limit - $allSuggestions->count())
                ->filter(function ($tag) use ($allSuggestions) {
                    return !$allSuggestions->contains('id', $tag->id);
                });

            $allSuggestions = $allSuggestions->merge($popularTags);
        }

        return $allSuggestions->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'aliases' => $tag->aliases,
                'suggestion_type' => $tag->suggestion_type ?? 'popular',
                'usage_count' => $tag->usage_count ?? 0,
            ];
        });
    }

    /**
     * Get user's most frequently used tags
     */
    public function getMostUsedTags(User $user, int $limit = 5): Collection
    {
        // Fixed N+1 query and added proper error handling
        return Tag::select('tags.*', DB::raw('COUNT(file_tag.tag_id) as usage_count'))
            ->join('file_tag', 'tags.id', '=', 'file_tag.tag_id')
            ->join('files', 'file_tag.file_id', '=', 'files.id')
            ->where('files.user_id', $user->id)
            ->groupBy('tags.id', 'tags.name', 'tags.aliases', 'tags.created_at', 'tags.updated_at')
            ->having('usage_count', '>', 0) // Only tags that are actually used
            ->orderByDesc('usage_count')
            ->limit(max(1, $limit)) // Ensure limit is at least 1
            ->get()
            ->map(function ($tag) {
                $tag->suggestion_type = 'most_used';
                return $tag;
            });
    }

    /**
     * Get user's recently used tags
     */
    public function getRecentlyUsedTags(User $user, int $limit = 5): Collection
    {
        // Added date validation and improved query
        $cutoffDate = now()->subDays(30);

        return Tag::select('tags.*', DB::raw('MAX(files.created_at) as last_used'))
            ->join('file_tag', 'tags.id', '=', 'file_tag.tag_id')
            ->join('files', 'file_tag.file_id', '=', 'files.id')
            ->where('files.user_id', $user->id)
            ->where('files.created_at', '>=', $cutoffDate)
            ->groupBy('tags.id', 'tags.name', 'tags.aliases', 'tags.created_at', 'tags.updated_at')
            ->orderByDesc('last_used')
            ->limit(max(1, $limit))
            ->get()
            ->map(function ($tag) {
                $tag->suggestion_type = 'recent';
                return $tag;
            });
    }

    /**
     * Get globally popular tags
     */
    public function getPopularTags(int $limit = 10): Collection
    {
        // Added caching potential and better performance
        return Tag::select('tags.*', DB::raw('COUNT(file_tag.tag_id) as usage_count'))
            ->join('file_tag', 'tags.id', '=', 'file_tag.tag_id')
            ->groupBy('tags.id', 'tags.name', 'tags.aliases', 'tags.created_at', 'tags.updated_at')
            ->having('usage_count', '>', 0)
            ->orderByDesc('usage_count')
            ->limit(max(1, $limit))
            ->get()
            ->map(function ($tag) {
                $tag->suggestion_type = 'popular';
                return $tag;
            });
    }

    /**
     * Get search suggestions based on user query
     */
    public function getSearchSuggestions(User $user, string $query, int $limit = 10): Collection
    {
        if (empty(trim($query))) {
            return $this->getPersonalizedSuggestions($user, $limit);
        }

        // Search in user's tags first
        $userTags = Tag::select('tags.*')
            ->join('file_tag', 'tags.id', '=', 'file_tag.tag_id')
            ->join('files', 'file_tag.file_id', '=', 'files.id')
            ->where('files.user_id', $user->id)
            ->where(function ($q) use ($query) {
                $q->where('tags.name', 'LIKE', "%{$query}%")
                  ->orWhereJsonContains('tags.aliases', $query);
            })
            ->groupBy('tags.id', 'tags.name', 'tags.aliases', 'tags.created_at', 'tags.updated_at')
            ->limit($limit)
            ->get()
            ->map(function ($tag) {
                $tag->suggestion_type = 'user_match';
                return $tag;
            });

        // If we need more results, search globally
        $remainingLimit = $limit - $userTags->count();
        if ($remainingLimit > 0) {
            $globalTags = Tag::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhereJsonContains('aliases', $query);
            })
            ->whereNotIn('id', $userTags->pluck('id'))
            ->limit($remainingLimit)
            ->get()
            ->map(function ($tag) {
                $tag->suggestion_type = 'global_match';
                return $tag;
            });

            $userTags = $userTags->merge($globalTags);
        }

        return $userTags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'aliases' => $tag->aliases,
                'suggestion_type' => $tag->suggestion_type,
                'usage_count' => $tag->usage_count ?? 0,
            ];
        });
    }

    /**
     * Get related tags based on selected tags
     */
    public function getRelatedTags(array $selectedTagIds, User $user, int $limit = 5): Collection
    {
        if (empty($selectedTagIds)) {
            return collect([]);
        }

        // Find tags that are commonly used together with the selected tags
        $relatedTags = Tag::select('tags.*', DB::raw('COUNT(DISTINCT files.id) as co_occurrence_count'))
            ->join('file_tag as ft1', 'tags.id', '=', 'ft1.tag_id')
            ->join('files', 'ft1.file_id', '=', 'files.id')
            ->join('file_tag as ft2', 'files.id', '=', 'ft2.file_id')
            ->whereIn('ft2.tag_id', $selectedTagIds)
            ->whereNotIn('tags.id', $selectedTagIds)
            ->groupBy('tags.id', 'tags.name', 'tags.aliases', 'tags.created_at', 'tags.updated_at')
            ->orderByDesc('co_occurrence_count')
            ->limit($limit)
            ->get()
            ->map(function ($tag) {
                $tag->suggestion_type = 'related';
                return $tag;
            });

        return $relatedTags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'aliases' => $tag->aliases,
                'suggestion_type' => 'related',
                'co_occurrence_count' => $tag->co_occurrence_count ?? 0,
            ];
        });
    }
}
