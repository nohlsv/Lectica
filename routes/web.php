<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\FileRecommendationController;
use Illuminate\Http\Request;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::get('home', function (Request $request) {
    $user = $request->user();
    $recommendationService = app(App\Services\FileRecommendationService::class);
    $recommendations = $recommendationService->getRecommendations($user);

    return Inertia::render('Dashboard', [
        'recommendations' => $recommendations
    ]);
})->middleware(['auth', 'verified'])->name('home');

// Programs routes available to all users
Route::get('/programs', [ProgramController::class, 'index'])
    ->name('programs.index');
Route::get('/programs/search', [ProgramController::class, 'search'])
    ->name('programs.search');

Route::middleware(['auth', 'verified'])->group(function () {
    require __DIR__ . '/files.php';
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
