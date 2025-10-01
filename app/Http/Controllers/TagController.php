<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Services\TagSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    /**
     * Get all tags for dropdown selection
     */
    public function index(Request $request)
    {
        $tags = Tag::orderBy('name')->get();

        // Return JSON for AJAX requests, redirect to appropriate page for browser requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($tags);
        }

        // Redirect to a proper page for browser requests
        return redirect()->route('home')->with('error', 'This endpoint is only available for AJAX requests.');
    }

    /**
     * Create a new tag
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
            'aliases' => 'nullable|array',
            'aliases.*' => 'string|max:50',
        ]);

        $tag = Tag::create($validated);

        // Return JSON for AJAX requests, redirect to appropriate page for browser requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($tag, 201);
        }

        // Redirect to a proper page for browser requests
        return redirect()->route('home')->with('success', 'Tag created successfully.');
    }

    /**
     * Update an existing tag
     */
    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,' . $tag->id,
            'aliases' => 'nullable|array',
            'aliases.*' => 'string|max:50',
        ]);

        $tag->update($validated);

        // Return JSON for AJAX requests, redirect to appropriate page for browser requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($tag);
        }

        // Redirect to a proper page for browser requests
        return redirect()->route('home')->with('success', 'Tag updated successfully.');
    }

    /**
     * Search for tags by name or aliases
     */
    public function search(Request $request)
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

        $formattedTags = $tags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'aliases' => $tag->aliases,
                'display_names' => $tag->getDisplayNames(),
                'searchable_terms' => $tag->getSearchableTerms(),
            ];
        });

        // Return JSON for AJAX requests, redirect to appropriate page for browser requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($formattedTags);
        }

        // Redirect to a proper page for browser requests
        return redirect()->route('home')->with('error', 'This endpoint is only available for AJAX requests.');
    }

    /**
     * Add an alias to a tag
     */
    public function addAlias(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'alias' => 'required|string|max:50|min:1',
        ]);

        try {
            $tag->addAlias($validated['alias']);
            $tag->save();

            $response = [
                'success' => true,
                'tag' => $tag->fresh(),
                'message' => 'Alias added successfully'
            ];

            // Return JSON for AJAX requests, redirect to appropriate page for browser requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($response);
            }

            return redirect()->route('home')->with('success', 'Alias added successfully.');
        } catch (\Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Failed to add alias: ' . $e->getMessage()
            ];

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($errorResponse, 500);
            }

            return redirect()->route('home')->with('error', 'Failed to add alias: ' . $e->getMessage());
        }
    }

    /**
     * Remove an alias from a tag
     */
    public function removeAlias(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'alias' => 'required|string|max:50',
        ]);

        try {
            $tag->removeAlias($validated['alias']);
            $tag->save();

            $response = [
                'success' => true,
                'tag' => $tag->fresh(),
                'message' => 'Alias removed successfully'
            ];

            // Return JSON for AJAX requests, redirect to appropriate page for browser requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($response);
            }

            return redirect()->route('home')->with('success', 'Alias removed successfully.');
        } catch (\Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Failed to remove alias: ' . $e->getMessage()
            ];

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($errorResponse, 500);
            }

            return redirect()->route('home')->with('error', 'Failed to remove alias: ' . $e->getMessage());
        }
    }

    /**
     * Get personalized tag suggestions for the authenticated user
     */
    public function suggestions(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
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

            $response = [
                'suggestions' => $suggestions,
                'query' => $query
            ];

            // Return JSON for AJAX requests, redirect to appropriate page for browser requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($response);
            }

            return redirect()->route('home')->with('error', 'This endpoint is only available for AJAX requests.');
        } catch (\Exception $e) {
            $errorResponse = [
                'error' => 'Failed to fetch suggestions',
                'message' => $e->getMessage()
            ];

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($errorResponse, 500);
            }

            return redirect()->route('home')->with('error', 'Failed to fetch suggestions: ' . $e->getMessage());
        }
    }

    /**
     * Get related tags based on currently selected tags
     */
    public function related(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
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

            $response = [
                'related_tags' => $relatedTags
            ];

            // Return JSON for AJAX requests, redirect to appropriate page for browser requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($response);
            }

            return redirect()->route('home')->with('error', 'This endpoint is only available for AJAX requests.');
        } catch (\Exception $e) {
            $errorResponse = [
                'error' => 'Failed to fetch related tags',
                'message' => $e->getMessage()
            ];

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($errorResponse, 500);
            }

            return redirect()->route('home')->with('error', 'Failed to fetch related tags: ' . $e->getMessage());
        }
    }
}
