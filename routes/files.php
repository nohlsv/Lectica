<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\FileStarController;
use App\Http\Controllers\FileRecommendationController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\PracticeRecordController;

// File stars
Route::post('/files/{file}/star', [FileStarController::class, 'toggle'])
    ->name('files.star');

Route::get('/files/{file}/download', [FileController::class, 'download'])
    ->name('files.download');

// Recommendations
Route::get('/recommendations', [FileRecommendationController::class, 'index'])
    ->name('recommendations.index');

// My Files
Route::get('/myfiles', [FileController::class, 'indexPersonal'])->name('myfiles');

Route::get('/files', [FileController::class, 'index'])->name('files.index');
Route::get('/files/create', [FileController::class, 'create'])->name('files.create');
Route::post('/files', [FileController::class, 'store'])->name('files.store');
Route::get('/files/{id}', [FileController::class, 'show'])->name('files.show');
Route::get('/files/{file}/edit', [FileController::class, 'edit'])
    ->name('files.edit')
    ->middleware('can:update,file');

Route::put('/files/{file}', [FileController::class, 'update'])
    ->name('files.update')
    ->middleware('can:update,file');
Route::delete('/files/{file}', [FileController::class, 'destroy'])
    ->name('files.destroy')
    ->middleware('can:delete,file');

// Tag routes
Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
Route::get('/tags/search', [TagController::class, 'search'])->name('tags.search');

// Flashcard routes
Route::get('/files/{file}/flashcards', [FlashcardController::class, 'index'])
    ->name('files.flashcards.index');
Route::get('/files/{file}/flashcards/create', [FlashcardController::class, 'create'])
    ->name('files.flashcards.create');
Route::post('/files/{file}/flashcards', [FlashcardController::class, 'store'])
    ->name('files.flashcards.store');
Route::get('/files/{file}/flashcards/{flashcard}', [FlashcardController::class, 'show'])
    ->name('files.flashcards.show');
Route::get('/files/{file}/flashcards/{flashcard}/edit', [FlashcardController::class, 'edit'])
    ->name('files.flashcards.edit');
Route::put('/files/{file}/flashcards/{flashcard}', [FlashcardController::class, 'update'])
    ->name('files.flashcards.update');
Route::delete('/files/{file}/flashcards/{flashcard}', [FlashcardController::class, 'destroy'])
    ->name('files.flashcards.destroy');
Route::get('/files/{file}/flashcards-practice', [FlashcardController::class, 'practice'])
    ->name('files.flashcards.practice');

// Quiz routes
Route::get('/files/{file}/quizzes', [QuizController::class, 'index'])
    ->name('files.quizzes.index');
Route::get('/files/{file}/quizzes/create', [QuizController::class, 'create'])
    ->name('files.quizzes.create');
Route::post('/files/{file}/quizzes', [QuizController::class, 'store'])
    ->name('files.quizzes.store');
Route::get('/files/{file}/quizzes/{quiz}', [QuizController::class, 'show'])
    ->name('files.quizzes.show');
Route::get('/files/{file}/quizzes/{quiz}/edit', [QuizController::class, 'edit'])
    ->name('files.quizzes.edit');
Route::put('/files/{file}/quizzes/{quiz}', [QuizController::class, 'update'])
    ->name('files.quizzes.update');
Route::delete('/files/{file}/quizzes/{quiz}', [QuizController::class, 'destroy'])
    ->name('files.quizzes.destroy');
Route::get('/files/{file}/quizzes-test', [QuizController::class, 'test'])
    ->name('files.quizzes.test');

Route::post('/files/{file}/generate-flashcards-quizzes', [FileController::class, 'generateFlashcardsAndQuizzes'])
    ->name('files.generate-flashcards-quizzes');

Route::get('/history', [PracticeRecordController::class, 'index'])->name('practice-records.index');
Route::get('/history/{practiceRecord}', [PracticeRecordController::class, 'show'])->name('practice-records.show');
Route::post('/practice-records', [PracticeRecordController::class, 'store'])->name('practice-records.store');

//Route::prefix('files/{file}')->name('files.')->group(function () {
//    // Flashcards routes
//    Route::prefix('flashcards')->name('flashcards.')->group(function () {
//        Route::get('/', 'FlashcardController@index')->name('index');
//        Route::get('/create', 'FlashcardController@create')->name('create');
//        Route::get('/practice', 'FlashcardController@practice')->name('practice');
//    });
//
//    // Quizzes routes
//    Route::prefix('quizzes')->name('quizzes.')->group(function () {
//        Route::get('/', 'QuizController@index')->name('index');
//        Route::get('/create', 'QuizController@create')->name('create');
//        Route::get('/test', 'QuizController@test')->name('test');
//    });
//});
