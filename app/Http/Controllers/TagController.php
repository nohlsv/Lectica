<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Services\TagSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    //
    /**
     * Get all tags for dropdown selection
     */
    public function index(): JsonResponse
    {
        $tags = Tag::orderBy('name')->get();
        return response()->json($tags);
    }

    /**
     * Create a new tag
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
            'aliases' => 'nullable|array',
            'aliases.*' => 'string|max:50',
        ]);

        $tag = Tag::create($validated);

        return response()->json($tag, 201);
    }

    /**
     * Update an existing tag
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,' . $tag->id,
            'aliases' => 'nullable|array',
            'aliases.*' => 'string|max:50',
        ]);

        $tag->update($validated);

        return response()->json($tag);
    }

    /**
     * Search for tags by name or aliases
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query', '');

        if (empty($query)) {
            $tags = Tag::orderBy('name')->limit(10)->get();
        } else {
            $tags = Tag::searchByNameOrAlias($query)
                ->orderBy('name')
                ->limit(10)
                ->get();
        }

        return response()->json($tags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'aliases' => $tag->aliases,
                'display_names' => $tag->getDisplayNames(),
                'searchable_terms' => $tag->getSearchableTerms(),
            ];
        }));
    }

    /**
     * Add an alias to a tag
     */
    public function addAlias(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'alias' => 'required|string|max:50|min:1',
        ]);

        try {
            $tag->addAlias($validated['alias']);
            $tag->save();

            return response()->json([
                'success' => true,
                'tag' => $tag->fresh(),
                'message' => 'Alias added successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add alias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove an alias from a tag
     */
    public function removeAlias(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'alias' => 'required|string|max:50',
        ]);

        try {
            $tag->removeAlias($validated['alias']);
            $tag->save();

            return response()->json([
                'success' => true,
                'tag' => $tag->fresh(),
                'message' => 'Alias removed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove alias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get personalized tag suggestions for the authenticated user
     */
    public function suggestions(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'query' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $validated['query'] ?? '';
        $limit = $validated['limit'] ?? 10;

        try {
            $tagSuggestionService = app(TagSuggestionService::class);

            if (empty($query)) {
                $suggestions = $tagSuggestionService->getPersonalizedSuggestions($user, $limit);
            } else {
                $suggestions = $tagSuggestionService->getSearchSuggestions($user, $query, $limit);
            }

            return response()->json([
                'suggestions' => $suggestions,
                'query' => $query
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch suggestions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get related tags based on currently selected tags
     */
    public function related(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'selected_tags' => 'nullable|array',
            'selected_tags.*' => 'integer|min:1',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $selectedTagIds = $validated['selected_tags'] ?? [];
        $limit = $validated['limit'] ?? 5;

        try {
            $tagSuggestionService = app(TagSuggestionService::class);
            $relatedTags = $tagSuggestionService->getRelatedTags($selectedTagIds, $user, $limit);

            return response()->json([
                'related_tags' => $relatedTags
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch related tags',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
