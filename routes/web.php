<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\FileRecommendationController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\TagController;
use App\Http\Controllers\GameController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::get('home', function (Request $request) {
    $user = $request->user();
    $recommendationService = app(App\Services\FileRecommendationService::class);
    $recommendations = $recommendationService->getRecommendations($user);
    logger()->info('Recommendations:', [
        'user_id' => $user->id,
        'recommendations' => $recommendations
    ]);

    return Inertia::render('Dashboard', [
        'recommendations' => $recommendations
    ]);
})->middleware(['auth', 'verified'])->name('home');

// Programs routes available to all users
Route::get('/programs', [ProgramController::class, 'index'])
    ->name('programs.index');
Route::get('/programs/search', [ProgramController::class, 'search'])
    ->name('programs.search');

Route::get('/tags', [TagController::class, 'index'])->name('tags.index');

// Game routes
Route::middleware(['auth', 'verified'])->group(function () {
//    Route::get('/battles', [App\Http\Controllers\BattleController::class, 'index'])->name('battles.index');
//    Route::post('/battles', [App\Http\Controllers\BattleController::class, 'store'])->name('battles.store');
    Route::get('/battles/{battle}', [App\Http\Controllers\BattleController::class, 'show'])->name('battles.show');

    Route::get('/games/lobby', [GameController::class, 'lobby'])->name('games.lobby');
    Route::post('/games', [GameController::class, 'store'])->name('games.store');
    Route::post('/games/{id}/join', [GameController::class, 'join'])->name('games.join');
    Route::get('/games/{id}', [GameController::class, 'show'])->name('games.show');
    Route::post('/games/{id}/start', [GameController::class, 'startQuizGame'])->name('games.start');
    Route::post('/games/{id}/finish', [GameController::class, 'finish'])->name('games.finish');
});

Route::middleware(['auth', 'verified'])->group(function () {
    require __DIR__ . '/files.php';
    Route::get('/statistics', [App\Http\Controllers\StatisticsController::class, 'index'])->name('statistics.index');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
