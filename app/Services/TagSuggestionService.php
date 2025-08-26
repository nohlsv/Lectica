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
        return Tag::select('tags.*', DB::raw('COUNT(file_tag.tag_id) as usage_count'))
            ->join('file_tag', 'tags.id', '=', 'file_tag.tag_id')
            ->join('files', 'file_tag.file_id', '=', 'files.id')
            ->where('files.user_id', $user->id)
            ->groupBy('tags.id', 'tags.name', 'tags.aliases', 'tags.created_at', 'tags.updated_at')
            ->orderByDesc('usage_count')
            ->limit($limit)
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
        return Tag::select('tags.*', DB::raw('MAX(files.created_at) as last_used'))
            ->join('file_tag', 'tags.id', '=', 'file_tag.tag_id')
            ->join('files', 'file_tag.file_id', '=', 'files.id')
            ->where('files.user_id', $user->id)
            ->where('files.created_at', '>=', now()->subDays(30)) // Only recent files
            ->groupBy('tags.id', 'tags.name', 'tags.aliases', 'tags.created_at', 'tags.updated_at')
            ->orderByDesc('last_used')
            ->limit($limit)
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
        return Tag::select('tags.*', DB::raw('COUNT(file_tag.tag_id) as usage_count'))
            ->join('file_tag', 'tags.id', '=', 'file_tag.tag_id')
            ->groupBy('tags.id', 'tags.name', 'tags.aliases', 'tags.created_at', 'tags.updated_at')
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get()
            ->map(function ($tag) {
                $tag->suggestion_type = 'popular';
                return $tag;
            });
    }

    /**
     * Get tag suggestions based on search query with personalization
     */
    public function getSearchSuggestions(User $user, string $query, int $limit = 10): Collection
    {
        if (empty($query)) {
            return $this->getPersonalizedSuggestions($user, $limit);
        }

        // First try to find matches in user's tags
        $userMatches = Tag::searchByNameOrAlias($query)
            ->join('file_tag', 'tags.id', '=', 'file_tag.tag_id')
            ->join('files', 'file_tag.file_id', '=', 'files.id')
            ->where('files.user_id', $user->id)
            ->select('tags.*', DB::raw('COUNT(file_tag.tag_id) as usage_count'))
            ->groupBy('tags.id', 'tags.name', 'tags.aliases', 'tags.created_at', 'tags.updated_at')
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get()
            ->map(function ($tag) {
                $tag->suggestion_type = 'user_match';
                return $tag;
            });

        // If we don't have enough user matches, search all tags
        if ($userMatches->count() < $limit) {
            $globalMatches = Tag::searchByNameOrAlias($query)
                ->select('tags.*', DB::raw('COUNT(file_tag.tag_id) as usage_count'))
                ->leftJoin('file_tag', 'tags.id', '=', 'file_tag.tag_id')
                ->groupBy('tags.id', 'tags.name', 'tags.aliases', 'tags.created_at', 'tags.updated_at')
                ->orderByDesc('usage_count')
                ->limit($limit - $userMatches->count())
                ->get()
                ->filter(function ($tag) use ($userMatches) {
                    return !$userMatches->contains('id', $tag->id);
                })
                ->map(function ($tag) {
                    $tag->suggestion_type = 'global_match';
                    return $tag;
                });

            $userMatches = $userMatches->merge($globalMatches);
        }

        return $userMatches->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'aliases' => $tag->aliases,
                'suggestion_type' => $tag->suggestion_type,
                'usage_count' => $tag->usage_count ?? 0,
                'display_names' => $tag->getDisplayNames(),
            ];
        });
    }

    /**
     * Get related tags based on tags commonly used together
     */
    public function getRelatedTags(array $selectedTagIds, User $user, int $limit = 5): Collection
    {
        if (empty($selectedTagIds)) {
            return collect();
        }

        return Tag::select('tags.*', DB::raw('COUNT(DISTINCT files.id) as co_occurrence'))
            ->join('file_tag as ft1', 'tags.id', '=', 'ft1.tag_id')
            ->join('files', 'ft1.file_id', '=', 'files.id')
            ->join('file_tag as ft2', 'files.id', '=', 'ft2.file_id')
            ->whereIn('ft2.tag_id', $selectedTagIds)
            ->whereNotIn('tags.id', $selectedTagIds)
            ->where('files.user_id', $user->id)
            ->groupBy('tags.id', 'tags.name', 'tags.aliases', 'tags.created_at', 'tags.updated_at')
            ->orderByDesc('co_occurrence')
            ->limit($limit)
            ->get()
            ->map(function ($tag) {
                $tag->suggestion_type = 'related';
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'aliases' => $tag->aliases,
                    'suggestion_type' => 'related',
                    'co_occurrence' => $tag->co_occurrence,
                ];
            });
    }
}
