<?php

namespace App\Http\Controllers;

use App\Http\Requests\FlashcardStoreRequest;
use App\Http\Requests\FlashcardUpdateRequest;
use App\Models\File;
use App\Models\Flashcard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FlashcardController extends Controller
{
    /**
     * Display a listing of the flashcards for a file.
     */
    public function index(File $file): Response
    {
        // Check if user can view the file
        if (! Gate::allows('view', $file)) {
            abort(403);
        }

        return Inertia::render('Flashcards/Index', [
            'file' => $file,
            'flashcards' => $file->flashcards,
        ]);
    }

    /**
     * Show the form for creating a new flashcard.
     */
    public function create(File $file): Response
    {
        // Check if user can update the file
        if (! Gate::allows('update', $file)) {
            abort(403);
        }

        return Inertia::render('Flashcards/Create', [
            'file' => $file,
        ]);
    }

    /**
     * Store a newly created flashcard in storage.
     */
    public function store(FlashcardStoreRequest $request, File $file): RedirectResponse
    {
        // Check if user can update the file
        if (! Gate::allows('update', $file)) {
            abort(403);
        }

        $file->flashcards()->create($request->validated());

        return redirect()->route('files.flashcards.index', $file)
            ->with('success', 'Flashcard created successfully.');
    }

    /**
     * Display the specified flashcard.
     */
    public function show(File $file, Flashcard $flashcard): Response
    {
        // Check if user can view the file
        if (! Gate::allows('view', $file)) {
            abort(403);
        }

        return Inertia::render('Flashcards/Show', [
            'file' => $file,
            'flashcard' => $flashcard,
        ]);
    }

    /**
     * Show the form for editing the specified flashcard.
     */
    public function edit(File $file, Flashcard $flashcard): Response
    {
        // Check if user can update the file
        if (! Gate::allows('update', $file)) {
            abort(403);
        }

        return Inertia::render('Flashcards/Edit', [
            'file' => $file,
            'flashcard' => $flashcard,
        ]);
    }

    /**
     * Update the specified flashcard in storage.
     */
    public function update(FlashcardUpdateRequest $request, File $file, Flashcard $flashcard): RedirectResponse
    {
        // Check if user can update the file
        if (! Gate::allows('update', $file)) {
            abort(403);
        }

        $flashcard->update($request->validated());

        return redirect()->route('files.flashcards.index', $file)
            ->with('success', 'Flashcard updated successfully.');
    }

    /**
     * Remove the specified flashcard from storage.
     */
    public function destroy(File $file, Flashcard $flashcard): RedirectResponse
    {
        // Check if user can update the file
        if (! Gate::allows('update', $file)) {
            abort(403);
        }

        $flashcard->delete();

        return redirect()->route('files.flashcards.index', $file)
            ->with('success', 'Flashcard deleted successfully.');
    }

    /**
     * Practice flashcards for a file.
     */
    public function practice(File $file): Response
    {
        // Check if user can view the file
        if (! Gate::allows('view', $file)) {
            abort(403);
        }

        return Inertia::render('Flashcards/Practice', [
            'file' => $file,
            'flashcards' => $file->flashcards,
        ]);
    }
}
