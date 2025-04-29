<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class FileRecommendationService
{
    // Cache TTL in minutes
    protected const CACHE_TTL = 60;

    /**
     * Get all recommendations for a user
     */
    public function getRecommendations(User $user, int $limit = 5): array
    {
        return Cache::remember(
            "recommendations:{$user->id}",
            Carbon::now()->addMinutes(self::CACHE_TTL),
            function () use ($user, $limit) {
                return [
                    'program' => $this->getProgramBasedRecommendations($user, $limit),
                    'collaborative' => $this->getCollaborativeRecommendations($user, $limit),
                    'contentBased' => $this->getContentBasedRecommendations($user, $limit),
                    'trending' => $this->getTrendingRecommendations($limit),
                ];
            }
        );
    }

    /**
     * Invalidate recommendations cache for a user
     */
    public function invalidateCache(User $user): void
    {
        Cache::forget("recommendations:{$user->id}");
    }

    /**
     * Get recommendations based on similar users in the same program
     */
    public function getProgramBasedRecommendations(User $user, int $limit = 5): Collection
    {
        if (!$user->program_id) {
            return new Collection();
        }

        return Cache::remember(
            "recommendations:program:{$user->id}",
            Carbon::now()->addMinutes(self::CACHE_TTL),
            function () use ($user, $limit) {
                // Get files starred by users in the same program
                return File::query()
                    ->select('files.*')
                    ->join('file_stars', 'files.id', '=', 'file_stars.file_id')
                    ->join('users', 'file_stars.user_id', '=', 'users.id')
                    ->where('users.program_id', $user->program_id)
                    ->where('users.id', '!=', $user->id)
                    ->whereNotIn('files.id', function($query) use ($user) {
                        $query->select('file_id')
                            ->from('file_stars')
                            ->where('user_id', $user->id);
                    })
                    ->groupBy('files.id')
                    ->orderByRaw('COUNT(*) DESC')
                    ->orderBy('file_stars.created_at', 'desc')
                    ->with(['tags', 'user', 'user.program'])
                    ->withCount('starredBy')
                    ->limit($limit)
                    ->get();
            }
        );
    }

    /**
     * Get recommendations based on files starred by users who starred the same files
     */
    public function getCollaborativeRecommendations(User $user, int $limit = 5): Collection
    {
        return Cache::remember(
            "recommendations:collaborative:{$user->id}",
            Carbon::now()->addMinutes(self::CACHE_TTL),
            function () use ($user, $limit) {
                // If user hasn't starred any files, return empty collection
                if ($user->starredFiles()->count() === 0) {
                    return new Collection();
                }

                return File::query()
                    ->select('files.*')
                    ->join('file_stars as fs1', 'files.id', '=', 'fs1.file_id')
                    ->whereExists(function ($query) use ($user) {
                        $query->select(DB::raw(1))
                            ->from('file_stars as fs2')
                            ->whereColumn('fs1.user_id', 'fs2.user_id')
                            ->whereIn('fs2.file_id', function($subquery) use ($user) {
                                $subquery->select('file_id')
                                    ->from('file_stars')
                                    ->where('user_id', $user->id);
                            });
                    })
                    ->whereNotIn('files.id', function($query) use ($user) {
                        $query->select('file_id')
                            ->from('file_stars')
                            ->where('user_id', $user->id);
                    })
                    ->groupBy('files.id')
                    ->orderByRaw('COUNT(*) DESC')
                    ->orderBy('fs1.created_at', 'desc')
                    ->with(['tags', 'user'])
                    ->withCount('starredBy')
                    ->limit($limit)
                    ->get();
            }
        );
    }

    /**
     * Get recommendations based on common tags
     */
    public function getContentBasedRecommendations(User $user, int $limit = 5): Collection
    {
        return Cache::remember(
            "recommendations:content:{$user->id}",
            Carbon::now()->addMinutes(self::CACHE_TTL),
            function () use ($user, $limit) {
                // If user hasn't starred any files, return empty collection
                if ($user->starredFiles()->count() === 0) {
                    return new Collection();
                }

                // Get tags from files the user has starred
                $userTags = $user->starredFiles()
                    ->with('tags')
                    ->get()
                    ->pluck('tags')
                    ->flatten()
                    ->pluck('id')
                    ->unique();

                if ($userTags->isEmpty()) {
                    return new Collection();
                }

                return File::query()
                    ->select('files.*')
                    ->join('file_tag', 'files.id', '=', 'file_tag.file_id')
                    ->whereIn('file_tag.tag_id', $userTags)
                    ->whereNotIn('files.id', function($query) use ($user) {
                        $query->select('file_id')
                            ->from('file_stars')
                            ->where('user_id', $user->id);
                    })
                    ->whereNotIn('files.id', $user->starredFiles()->pluck('id'))
                    ->groupBy('files.id')
                    ->orderByRaw('COUNT(*) DESC') // More matching tags = higher ranking
                    ->with(['tags', 'user'])
                    ->withCount('starredBy')
                    ->limit($limit)
                    ->get();
            }
        );
    }

    /**
     * Get trending files based on recent star activity
     */
    public function getTrendingRecommendations(int $limit = 5): Collection
    {
        return Cache::remember(
            "recommendations:trending",
            Carbon::now()->addMinutes(5), // Shorter cache for trending
            function () use ($limit) {
                return File::query()
                    ->select('files.*')
                    ->join('file_stars', 'files.id', '=', 'file_stars.file_id')
                    ->where('file_stars.created_at', '>', now()->subDays(7))
                    ->groupBy('files.id')
                    ->orderByRaw('COUNT(*) DESC')
                    ->with(['tags', 'user'])
                    ->withCount('starredBy')
                    ->limit($limit)
                    ->get();
            }
        );
    }
}
