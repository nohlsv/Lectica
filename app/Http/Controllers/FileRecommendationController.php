<?php

namespace App\Http\Controllers;

use App\Services\FileRecommendationService;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FileRecommendationController extends Controller
{
    public function __construct(
        private FileRecommendationService $recommendationService
    ) {}

    /**
     * Display file recommendations
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $recommendations = $this->recommendationService->getRecommendations($user);

        return Inertia::render('Files/Recommendations', [
            'recommendations' => $recommendations,
            'userProgram' => $user->program ? $user->program->name : null,
        ]);
    }
}
