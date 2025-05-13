<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Flashcard;
use App\Models\Tag; // Import Tag model
use App\Models\Program; // Import Program model
use Inertia\Inertia;
use Inertia\Response;

class StatisticsController extends Controller
{
	/**
	 * Display the statistics page.
	 */
	public function index(): Response
	{
		$statistics = [
			'total_users' => User::count(),
			'total_files' => File::count(),
			'total_quizzes' => Quiz::count(),
			'total_flashcards' => Flashcard::count(),
			'total_tags' => Tag::count(), // New statistic
			'total_programs' => Program::count(), // New statistic
			'most_used_tags' => Tag::withCount('files')->orderBy('files_count', 'desc')->take(5)->get(),
			'most_files_per_program' => Program::with(['users.files'])
				->get()
				->map(function ($program) {
					$program->files_count = $program->users->sum(function ($user) {
						return $user->files->count();
					});
					return $program;
				})
				->sortByDesc('files_count')
				->take(5)
				->values(),
			'most_active_user' => User::withCount('files')->orderBy('files_count', 'desc')->first(),
			'average_files_per_user' => round(File::count() / max(User::count(), 1), 2), // New statistic
			'total_flashcards_per_tag' => Tag::with('files.flashcards')
				->get()
				->map(function ($tag) {
					$tag->flashcards_count = $tag->files->sum(function ($file) {
						return $file->flashcards->count();
					});
					return $tag;
				})
				->sortByDesc('flashcards_count')
				->take(5)
				->values(),
			'total_quizzes_per_tag' => Tag::with('files.quizzes')
				->get()
				->map(function ($tag) {
					$tag->quizzes_count = $tag->files->sum(function ($file) {
						return $file->quizzes->count();
					});
					return $tag;
				})
				->sortByDesc('quizzes_count')
				->take(5)
				->values(),
			'most_quizzes_by_user' => User::with('files.quizzes')
				->get()
				->map(function ($user) {
					$user->quizzes_count = $user->files->sum(function ($file) {
						return $file->quizzes->count();
					});
					return $user;
				})
				->sortByDesc('quizzes_count')
				->first(),
			'user_with_most_stars' => User::with('files.starredBy')
				->get()
				->map(function ($user) {
					$user->files_sum_stars = $user->files->sum(function ($file) {
						return $file->starredBy->count(); // Count the number of stars for each file
					});
					return [
						'id' => $user->id,
						'name' => $user->name,
						'files_sum_stars' => $user->files_sum_stars,
					];
				})
				->sortByDesc('files_sum_stars')
				->first(),
			'average_flashcards_per_quiz' => round(Flashcard::count() / max(Quiz::count(), 1), 2), // New statistic
		];

		return Inertia::render('Statistics/Index', [
			'statistics' => $statistics,
		]);
	}
}
