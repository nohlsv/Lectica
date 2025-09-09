<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\FileRecommendationController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\TagController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\CollectionController;

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
Route::get('/tags/search', [TagController::class, 'search'])->name('tags.search');

// Tag suggestion routes (for authenticated users)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tags/suggestions', [TagController::class, 'suggestions'])->name('tags.suggestions');
    Route::get('/tags/related', [TagController::class, 'related'])->name('tags.related');
});

// Tag alias management routes (for admin/faculty)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::put('/tags/{tag}', [TagController::class, 'update'])->name('tags.update');
    Route::post('/tags/{tag}/aliases', [TagController::class, 'addAlias'])->name('tags.addAlias');
    Route::delete('/tags/{tag}/aliases', [TagController::class, 'removeAlias'])->name('tags.removeAlias');
});

// Game routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Battle routes - complete battle system
    Route::get('/battles', [App\Http\Controllers\BattleController::class, 'index'])->name('battles.index');
    Route::get('/battles/create', [App\Http\Controllers\BattleController::class, 'create'])->name('battles.create');
    Route::post('/battles', [App\Http\Controllers\BattleController::class, 'store'])->name('battles.store');
    Route::get('/battles/{battle}', [App\Http\Controllers\BattleController::class, 'show'])->name('battles.show');
    Route::post('/battles/{battle}/answer', [App\Http\Controllers\BattleController::class, 'answerQuestion'])->name('battles.answer');
    Route::post('/battles/{battle}/end', [App\Http\Controllers\BattleController::class, 'end'])->name('battles.end');
    Route::post('/battles/complete', [App\Http\Controllers\BattleController::class, 'complete'])->name('battles.complete');
    Route::get('/battle-stats', [App\Http\Controllers\BattleController::class, 'stats'])->name('battles.stats');
    Route::get('/api/monsters', [App\Http\Controllers\BattleController::class, 'getMonstersByDifficulty'])->name('api.monsters');

    // Collection routes - playlist-like file management system
    Route::get('/user/collections', [App\Http\Controllers\CollectionController::class, 'userCollections'])->name('collections.user');
    Route::get('/collections', [App\Http\Controllers\CollectionController::class, 'index'])->name('collections.index');
    Route::get('/collections/create', [App\Http\Controllers\CollectionController::class, 'create'])->name('collections.create');
    Route::post('/collections', [App\Http\Controllers\CollectionController::class, 'store'])->name('collections.store');
    Route::get('/collections/browse', [App\Http\Controllers\CollectionController::class, 'browse'])->name('collections.browse');
    Route::get('/collections/{collection}', [App\Http\Controllers\CollectionController::class, 'show'])->name('collections.show');
    Route::get('/collections/{collection}/edit', [App\Http\Controllers\CollectionController::class, 'edit'])->name('collections.edit');
    Route::put('/collections/{collection}', [App\Http\Controllers\CollectionController::class, 'update'])->name('collections.update');
    Route::delete('/collections/{collection}', [App\Http\Controllers\CollectionController::class, 'destroy'])->name('collections.destroy');
    Route::post('/collections/{collection}/files', [App\Http\Controllers\CollectionController::class, 'addFile'])->name('collections.add-file');
    Route::delete('/collections/{collection}/files', [App\Http\Controllers\CollectionController::class, 'removeFile'])->name('collections.remove-file');
    Route::patch('/collections/{collection}/reorder', [App\Http\Controllers\CollectionController::class, 'reorderFiles'])->name('collections.reorder');
    Route::post('/collections/{collection}/copy', [App\Http\Controllers\CollectionController::class, 'copy'])->name('collections.copy');
    Route::post('/collections/{collection}/favorite', [App\Http\Controllers\CollectionController::class, 'toggleFavorite'])->name('collections.favorite');

    // Multiplayer Game routes - battle-like multiplayer system
    Route::get('/multiplayer-games', [App\Http\Controllers\MultiplayerGameController::class, 'index'])->name('multiplayer-games.index');
    Route::get('/multiplayer-games/create', [App\Http\Controllers\MultiplayerGameController::class, 'create'])->name('multiplayer-games.create');
    Route::post('/multiplayer-games', [App\Http\Controllers\MultiplayerGameController::class, 'store'])->name('multiplayer-games.store');
    Route::get('/multiplayer-games/lobby', [App\Http\Controllers\MultiplayerGameController::class, 'lobby'])->name('multiplayer-games.lobby');
    Route::get('/multiplayer-games/{multiplayerGame}', [App\Http\Controllers\MultiplayerGameController::class, 'show'])->name('multiplayer-games.show');
    Route::post('/multiplayer-games/{multiplayerGame}/join', [App\Http\Controllers\MultiplayerGameController::class, 'join'])->name('multiplayer-games.join');
    Route::post('/multiplayer-games/{multiplayerGame}/answer', [App\Http\Controllers\MultiplayerGameController::class, 'answerQuestion'])->name('multiplayer-games.answer');
    Route::post('/multiplayer-games/{multiplayerGame}/abandon', [App\Http\Controllers\MultiplayerGameController::class, 'abandon'])->name('multiplayer-games.abandon');

    // Quest routes
    Route::get('/quests', [App\Http\Controllers\QuestController::class, 'index'])->name('quests.index');
    Route::get('/quests/stats', [App\Http\Controllers\QuestController::class, 'stats'])->name('quests.stats');
    Route::get('/api/quests/progress', [App\Http\Controllers\QuestController::class, 'progress'])->name('api.quests.progress');

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
    Route::get('/leaderboards', function () {
        return Inertia::render('Leaderboards');
    })->name('leaderboards');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
