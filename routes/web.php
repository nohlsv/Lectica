<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    require __DIR__ . '/files.php';

// Programs
    Route::get('/programs', [ProgramController::class, 'index'])
        ->name('programs.index');
    Route::get('/programs/search', [ProgramController::class, 'search'])
        ->name('programs.search');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
