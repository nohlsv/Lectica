<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollectionController;

Route::middleware('auth')->get('/user', function (Request $request) {
    return $request->user();
});

// Collection API routes
Route::middleware('auth')->group(function () {
    Route::get('/user/collections', function (Request $request) {
        return $request->user()->collections()
            ->select('id', 'name', 'file_count', 'is_public')
            ->orderBy('name')
            ->get();
    });

    // Add route for creating collections
    Route::post('/collections', [CollectionController::class, 'store']);
});
