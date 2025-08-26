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
            'alias' => 'required|string|max:50',
        ]);

        $tag->addAlias($validated['alias'])->save();

        return response()->json($tag);
    }

    /**
     * Remove an alias from a tag
     */
    public function removeAlias(Request $request, Tag $tag): JsonResponse
    {
        $validated = $request->validate([
            'alias' => 'required|string|max:50',
        ]);

        $tag->removeAlias($validated['alias'])->save();

        return response()->json($tag);
    }

    /**
     * Get personalized tag suggestions for the authenticated user
     */
    public function suggestions(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = $request->input('query', '');
        $limit = $request->input('limit', 10);

        $tagSuggestionService = app(TagSuggestionService::class);

        if (empty($query)) {
            // Return personalized suggestions (recent + most used + popular)
            $suggestions = $tagSuggestionService->getPersonalizedSuggestions($user, $limit);
        } else {
            // Return search results with personalization
            $suggestions = $tagSuggestionService->getSearchSuggestions($user, $query, $limit);
        }

        return response()->json([
            'suggestions' => $suggestions,
            'query' => $query
        ]);
    }

    /**
     * Get related tags based on currently selected tags
     */
    public function related(Request $request): JsonResponse
    {
        $user = $request->user();
        $selectedTagIds = $request->input('selected_tags', []);
        $limit = $request->input('limit', 5);

        $tagSuggestionService = app(TagSuggestionService::class);
        $relatedTags = $tagSuggestionService->getRelatedTags($selectedTagIds, $user, $limit);

        return response()->json([
            'related_tags' => $relatedTags
        ]);
    }
}
