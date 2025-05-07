<?php

namespace App\Http\Controllers;

use App\Enums\QuizType;
use App\Http\Requests\QuizStoreRequest;
use App\Http\Requests\QuizUpdateRequest;
use App\Models\File;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class QuizController extends Controller
{
    /**
     * Display a listing of the quizzes for a file.
     */
    public function index(File $file): Response
    {
        // Check if user can view the file
        if (! Gate::allows('view', $file)) {
            abort(403);
        }

        return Inertia::render('Quizzes/Index', [
            'file' => $file,
            'quizzes' => $file->quizzes,
            'quizTypes' => QuizType::labels(),
        ]);
    }

    /**
     * Show the form for creating a new quiz.
     */
    public function create(File $file): Response
    {
        // Check if user can update the file
        if (! Gate::allows('update', $file)) {
            abort(403);
        }

        return Inertia::render('Quizzes/Create', [
            'file' => $file,
            'quizTypes' => QuizType::labels(),
        ]);
    }

    /**
     * Store a newly created quiz in storage.
     */
    public function store(QuizStoreRequest $request, File $file): RedirectResponse
    {
        // Check if user can update the file
        if (! Gate::allows('update', $file)) {
            abort(403);
        }

        $file->quizzes()->create($request->validated());

        return redirect()->route('files.quizzes.index', $file)
            ->with('success', 'Quiz created successfully.');
    }

    /**
     * Display the specified quiz.
     */
    public function show(File $file, Quiz $quiz): Response
    {
        // Check if user can view the file
        if (! Gate::allows('view', $file)) {
            abort(403);
        }

        return Inertia::render('Quizzes/Show', [
            'file' => $file,
            'quiz' => $quiz,
            'quizTypes' => QuizType::labels(),
        ]);
    }

    /**
     * Show the form for editing the specified quiz.
     */
    public function edit(File $file, Quiz $quiz): Response
    {
        // Check if user can update the file
        if (! Gate::allows('update', $file)) {
            abort(403);
        }

        return Inertia::render('Quizzes/Edit', [
            'file' => $file,
            'quiz' => $quiz,
            'quizTypes' => QuizType::labels(),
        ]);
    }

    /**
     * Update the specified quiz in storage.
     */
    public function update(QuizUpdateRequest $request, File $file, Quiz $quiz): RedirectResponse
    {
        // Check if user can update the file
        if (! Gate::allows('update', $file)) {
            abort(403);
        }

        $quiz->update($request->validated());

        return redirect()->route('files.quizzes.index', $file)
            ->with('success', 'Quiz updated successfully.');
    }

    /**
     * Remove the specified quiz from storage.
     */
    public function destroy(File $file, Quiz $quiz): RedirectResponse
    {
        // Check if user can update the file
        if (! Gate::allows('update', $file)) {
            abort(403);
        }

        $quiz->delete();

        return redirect()->route('files.quizzes.index', $file)
            ->with('success', 'Quiz deleted successfully.');
    }

    /**
     * Take the quizzes for a file.
     */
    public function test(File $file): Response
    {
        // Check if user can view the file
        if (! Gate::allows('view', $file)) {
            abort(403);
        }

        return Inertia::render('Quizzes/Test', [
            'file' => $file,
            'quizzes' => $file->quizzes,
            'quizTypes' => QuizType::labels(),
        ]);
    }
}
