<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\FileRecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FileStarController extends Controller
{
    public function __construct(
        private readonly FileRecommendationService $recommendationService
    ) {}

    /**
     * Toggle star status for a file
     */
    public function toggle(Request $request, File $file): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasStarred($file)) {
            $user->starredFiles()->detach($file->id);
            $message = 'File has been unstarred.';
        } else {
            $user->starredFiles()->attach($file->id);
            $message = 'File has been starred.';
        }

        // Invalidate recommendation cache
        $this->recommendationService->invalidateCache($user);

        return back()->with('success', $message);
    }
}
