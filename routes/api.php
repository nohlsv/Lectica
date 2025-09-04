<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CollectionController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Collection API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/collections', function (Request $request) {
        return $request->user()->collections()
            ->select('id', 'name', 'file_count', 'is_public')
            ->orderBy('name')
            ->get();
    });
});
