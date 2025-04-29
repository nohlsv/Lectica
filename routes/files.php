<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\TagController;

Route::get('/files', [FileController::class, 'index'])->name('files.index');
Route::get('/files/create', [FileController::class, 'create'])->name('files.create');
Route::post('/files', [FileController::class, 'store'])->name('files.store');
Route::get('/files/{id}', [FileController::class, 'show'])->name('files.show');
Route::get('/files/{id}/edit', [FileController::class, 'edit'])->name('files.edit');
Route::put('/files/{id}', [FileController::class, 'update'])->name('files.update');
Route::delete('/files/{id}', [FileController::class, 'destroy'])->name('files.destroy');

// Tag routes
Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
Route::get('/tags/search', [TagController::class, 'search'])->name('tags.search');
