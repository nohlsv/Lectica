<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\LeaderboardController;

Route::middleware('auth')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::middleware('auth')->group(function () {
//    // User's own collections (for modal in Files/Show.vue)
//    Route::get('/user/collections', function () {
//        return App\Models\Collection::where('user_id', Auth::id())
//            ->select('id', 'name', 'file_count', 'is_public')
//            ->get();
//    });
//
//    // Custom actions for collections (do NOT use the 'collections.' prefix to avoid name conflicts)
//    Route::post('collections/{collection}/add-file', [CollectionController::class, 'addFile']);
//    Route::post('collections/{collection}/remove-file', [CollectionController::class, 'removeFile']);
//    Route::post('collections/{collection}/reorder-files', [CollectionController::class, 'reorderFiles']);
//    Route::post('collections/{collection}/toggle-favorite', [CollectionController::class, 'toggleFavorite']);
//    Route::post('collections/{collection}/copy', [CollectionController::class, 'copy']);
//    Route::get('collections/browse', [CollectionController::class, 'browse']);
//    // List all collections (for API)
//    Route::get('collections', [CollectionController::class, 'index']);
//    // Create a new collection (for API)
//    Route::post('collections', [CollectionController::class, 'store']);
//});

Route::get('/leaderboard/general', [LeaderboardController::class, 'general']);
Route::get('/leaderboard/multiplayer', [LeaderboardController::class, 'multiplayer']);
