<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\File;
use App\Models\Monster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    /**
     * Display a listing of collections.
     */
    public function index(Request $request)
    {
        $query = Collection::with(['user', 'files'])
            ->accessible()
            ->withCount(['files', 'favoritedBy']);

        // Filter by type
        $type = $request->get('type', 'all');
        switch ($type) {
            case 'owned':
                $query->owned();
                break;
            case 'favorited':
                $query->favorited();
                break;
            case 'public':
                $query->public();
                break;
            case 'copied':
                $query->owned()->where('is_original', false);
                break;
        }

        // Search functionality
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // Tag filtering
        if ($tags = $request->get('tags')) {
            $tagsArray = is_array($tags) ? $tags : explode(',', $tags);
            $query->withTags($tagsArray);
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        switch ($sort) {
            case 'name':
                $query->orderBy('name', $direction);
                break;
            case 'files':
                $query->orderBy('file_count', $direction);
                break;
            case 'popularity':
                $query->orderBy('copy_count', $direction);
                break;
            default:
                $query->orderBy('created_at', $direction);
        }

        $collections = $query->paginate(12);

        return Inertia::render('Collections/Index', [
            'collections' => $collections,
            'filters' => [
                'type' => $type,
                'search' => $search,
                'tags' => $tags,
                'sort' => $sort,
                'direction' => $direction,
            ]
        ]);
    }

    /**
     * Show the form for creating a new collection.
     */
    public function create()
    {
        $userFiles = File::where('user_id', Auth::id())
            ->with(['quizzes'])
            ->get();

        return Inertia::render('Collections/Create', [
            'userFiles' => $userFiles
        ]);
    }

    /**
     * Store a newly created collection.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'file_ids' => 'nullable|array',
            'file_ids.*' => 'exists:files,id',
        ]);

        $collection = DB::transaction(function () use ($request) {
            $collection = Collection::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'description' => $request->description,
                'is_public' => $request->boolean('is_public', false),
                'tags' => $request->tags ?? [],
            ]);

            // Add files to collection if provided
            if ($request->file_ids) {
                foreach ($request->file_ids as $index => $fileId) {
                    // Verify user owns the file
                    $file = File::where('id', $fileId)
                        ->where('user_id', Auth::id())
                        ->first();

                    if ($file) {
                        $collection->files()->attach($fileId, ['order' => $index]);
                    }
                }

                $collection->updateCounts();
            }

            return $collection;
        });

        // Return JSON response for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'id' => $collection->id,
                'name' => $collection->name,
                'file_count' => $collection->file_count ?? 0,
                'is_public' => $collection->is_public,
                'message' => 'Collection created successfully!'
            ], 201);
        }

        // Return redirect for web requests
        return redirect()->route('collections.index')
            ->with('success', 'Collection created successfully!');
    }

    /**
     * Display the specified collection.
     */
    public function show(Collection $collection)
    {
        // Check if user can view this collection
        if (!$collection->is_public && $collection->user_id !== Auth::id()) {
            abort(403, 'This collection is private.');
        }

        $collection->load([
            'user',
            'files.user',
            'files.quizzes',
            'originalCollection.user',
            'originalCreator'
        ]);

        $monsters = Monster::all();

        return Inertia::render('Collections/Show', [
            'collection' => $collection,
            'monsters' => $monsters,
            'canEdit' => $collection->can_edit,
            'canCopy' => $collection->can_copy,
        ]);
    }

    /**
     * Show the form for editing the specified collection.
     */
    public function edit(Collection $collection)
    {
        // Check if user can edit this collection
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'You can only edit your own collections.');
        }

        $collection->load('files');
        $userFiles = File::where('user_id', Auth::id())
            ->with(['quizzes'])
            ->get();

        return Inertia::render('Collections/Edit', [
            'collection' => $collection,
            'userFiles' => $userFiles
        ]);
    }

    /**
     * Update the specified collection.
     */
    public function update(Request $request, Collection $collection)
    {
        // Check if user can edit this collection
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'You can only edit your own collections.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'file_ids' => 'nullable|array',
            'file_ids.*' => 'exists:files,id',
        ]);

        DB::transaction(function () use ($request, $collection) {
            $collection->update([
                'name' => $request->name,
                'description' => $request->description,
                'is_public' => $request->boolean('is_public', false),
                'tags' => $request->tags ?? [],
            ]);

            // Update files in collection
            $collection->files()->detach();

            if ($request->file_ids) {
                foreach ($request->file_ids as $index => $fileId) {
                    // Verify user owns the file
                    $file = File::where('id', $fileId)
                        ->where('user_id', Auth::id())
                        ->first();

                    if ($file) {
                        $collection->files()->attach($fileId, ['order' => $index]);
                    }
                }
            }

            $collection->updateCounts();
        });

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Collection updated successfully!');
    }

    /**
     * Remove the specified collection from storage.
     */
    public function destroy(Collection $collection)
    {
        // Check if user can delete this collection
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'You can only delete your own collections.');
        }

        $collection->delete();

        return redirect()->route('collections.index')
            ->with('success', 'Collection deleted successfully!');
    }

    /**
     * Add a file to a collection.
     */
    public function addFile(Request $request, Collection $collection)
    {
        // Check if user can edit this collection
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'You can only edit your own collections.');
        }

        $request->validate([
            'file_id' => 'required|exists:files,id',
        ]);

        $file = File::findOrFail($request->file_id);

        // Verify user owns the file
        if ($file->user_id !== Auth::id()) {
            abort(403, 'You can only add your own files to collections.');
        }

        // Check if file is already in collection
        if ($collection->files()->where('file_id', $file->id)->exists()) {
            return back()->withErrors(['file' => 'File is already in this collection.']);
        }

        $collection->addFile($file);

        return back()->with('success', 'File added to collection successfully!');
    }

    /**
     * Remove a file from a collection.
     */
    public function removeFile(Request $request, Collection $collection)
    {
        // Check if user can edit this collection
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'You can only edit your own collections.');
        }

        $request->validate([
            'file_id' => 'required|exists:files,id',
        ]);

        $file = File::findOrFail($request->file_id);
        $collection->removeFile($file);

        return back()->with('success', 'File removed from collection successfully!');
    }

    /**
     * Reorder files in a collection.
     */
    public function reorderFiles(Request $request, Collection $collection)
    {
        // Check if user can edit this collection
        if ($collection->user_id !== Auth::id()) {
            abort(403, 'You can only edit your own collections.');
        }

        $request->validate([
            'file_orders' => 'required|array',
            'file_orders.*.file_id' => 'required|exists:files,id',
            'file_orders.*.order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $collection) {
            foreach ($request->file_orders as $fileOrder) {
                $collection->files()->updateExistingPivot(
                    $fileOrder['file_id'],
                    ['order' => $fileOrder['order']]
                );
            }
        });

        return back()->with('success', 'Files reordered successfully!');
    }

    /**
     * Copy a collection to the current user's account.
     */
    public function copy(Request $request, Collection $collection)
    {
        // Check if collection can be copied
        if (!$collection->can_copy) {
            abort(403, 'This collection cannot be copied.');
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
        ]);

        $newName = $request->name ?: null;
        $copy = $collection->createCopy(Auth::user(), $newName);

        return redirect()->route('collections.show', $copy)
            ->with('success', 'Collection copied successfully!');
    }

    /**
     * Toggle favorite status for a collection.
     */
    public function toggleFavorite(Collection $collection)
    {
        // Check if collection is accessible
        if (!$collection->is_public && $collection->user_id !== Auth::id()) {
            abort(403, 'This collection is not accessible.');
        }

        $isFavorited = $collection->toggleFavorite();

        return response()->json([
            'is_favorited' => $isFavorited,
            'message' => $isFavorited ? 'Added to favorites!' : 'Removed from favorites!'
        ]);
    }

    /**
     * Browse public collections.
     */
    public function browse(Request $request)
    {
        $query = Collection::with(['user', 'files'])
            ->public()
            ->original()
            ->withCount(['files', 'favoritedBy']);

        // Search functionality
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // Tag filtering
        if ($tags = $request->get('tags')) {
            $tagsArray = is_array($tags) ? $tags : explode(',', $tags);
            $query->withTags($tagsArray);
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        switch ($sort) {
            case 'name':
                $query->orderBy('name', $direction);
                break;
            case 'files':
                $query->orderBy('file_count', $direction);
                break;
            case 'popularity':
                $query->orderBy('copy_count', $direction);
                break;
            default:
                $query->orderBy('created_at', $direction);
        }

        $collections = $query->paginate(12);

        return Inertia::render('Collections/Browse', [
            'collections' => $collections,
            'filters' => [
                'search' => $search,
                'tags' => $tags,
                'sort' => $sort,
                'direction' => $direction,
            ]
        ]);
    }

    public function userCollections(Collection $collection) {
        $collections = Collection::where('user_id', Auth::id())
            ->select('id', 'name', 'file_count', 'is_public')
            ->get();

        return response()->json($collections);
    }
}
