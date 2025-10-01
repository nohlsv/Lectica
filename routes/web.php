<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\FileRecommendationController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\TagController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('welcome');

Route::get('home', function (Request $request) {
    $user = $request->user();
    $recommendationService = app(App\Services\FileRecommendationService::class);
    $streakService = app(App\Services\StudyStreakService::class);
    
    $recommendations = $recommendationService->getRecommendations($user);
    $streakStats = $streakService->getStreakStats($user);
    
    logger()->info('Recommendations:', [
        'user_id' => $user->id,
        'recommendations' => $recommendations
    ]);
    
    logger()->info('Streak Stats:', [
        'user_id' => $user->id,
        'current_streak' => $streakStats['current_streak'],
        'heatmap_count' => count($streakStats['heatmap_data']),
        'sample_heatmap_data' => array_slice($streakStats['heatmap_data'], 0, 5)
    ]);

    return Inertia::render('Dashboard', [
        'recommendations' => $recommendations,
        'streakStats' => $streakStats
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
    Route::get('/api/question-counts', [App\Http\Controllers\BattleController::class, 'getQuestionCounts'])->name('api.question-counts');

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
    Route::get('/multiplayer-games/{multiplayerGame}/state', [App\Http\Controllers\MultiplayerGameController::class, 'getGameState'])->name('multiplayer-games.state');
    Route::post('/multiplayer-games/{multiplayerGame}/ping', [App\Http\Controllers\MultiplayerGameController::class, 'pingGameState'])->name('multiplayer-games.ping');

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
    Route::get('/faq', function () {
        return Inertia::render('FAQ');
    })->name('faq');
    
    // Debug route for testing streak data
    Route::get('/debug-streaks', function (Request $request) {
        $user = $request->user();
        $streakService = app(App\Services\StudyStreakService::class);
        $stats = $streakService->getStreakStats($user);
        
        // Also get raw activity data for debugging
        $recentActivities = App\Models\UserStudyActivity::where('user_id', $user->id)
            ->orderBy('study_date', 'desc')
            ->take(10)
            ->pluck('study_date')
            ->map(fn($date) => $date->format('Y-m-d'));
        
        return response()->json([
            'current_streak' => $stats['current_streak'],
            'longest_streak' => $stats['longest_streak'], 
            'total_study_days' => $stats['total_study_days'],
            'heatmap_count' => count($stats['heatmap_data']),
            'recent_activities' => $recentActivities,
            'sample_recent_data' => array_slice($stats['heatmap_data'], -10),
            'sample_early_data' => array_slice($stats['heatmap_data'], 0, 10)
        ]);
    });

    Route::get('/admin/user-roles', [UserController::class, 'show'])
        ->name('admin.user-roles');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
