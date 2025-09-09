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
						'first_name' => $user->first_name,
						'last_name' => $user->last_name,
						'files_sum_stars' => $user->files_sum_stars,
					];
				})
				->sortByDesc('files_sum_stars')
				->first(),
			'average_flashcards_per_quiz' => round(Flashcard::count() / max(Quiz::count(), 1), 2), // New statistic
			'new_users_7d' => User::where('created_at', '>=', now()->subDays(7))->count(),
			'new_files_7d' => File::where('created_at', '>=', now()->subDays(7))->count(),
			'new_quizzes_7d' => Quiz::where('created_at', '>=', now()->subDays(7))->count(),
			'new_flashcards_7d' => Flashcard::where('created_at', '>=', now()->subDays(7))->count(),
			'new_tags_7d' => Tag::where('created_at', '>=', now()->subDays(7))->count(),
			'new_programs_7d' => Program::where('created_at', '>=', now()->subDays(7))->count(),

			'new_users_30d' => User::where('created_at', '>=', now()->subDays(30))->count(),
			'new_files_30d' => File::where('created_at', '>=', now()->subDays(30))->count(),
			'new_quizzes_30d' => Quiz::where('created_at', '>=', now()->subDays(30))->count(),
			'new_flashcards_30d' => Flashcard::where('created_at', '>=', now()->subDays(30))->count(),
			'new_tags_30d' => Tag::where('created_at', '>=', now()->subDays(30))->count(),
			'new_programs_30d' => Program::where('created_at', '>=', now()->subDays(30))->count(),

			'latest_users' => User::orderBy('created_at', 'desc')->take(5)->get(['id','first_name','last_name','created_at']),
			'latest_files' => File::orderBy('created_at', 'desc')->take(5)->get(['id','name','created_at']),
			'latest_quizzes' => Quiz::orderBy('created_at', 'desc')->take(5)->get(['id','name','created_at']),
			'latest_flashcards' => Flashcard::orderBy('created_at', 'desc')->take(5)->get(['id','question','created_at']),
			'latest_tags' => Tag::orderBy('created_at', 'desc')->take(5)->get(['id','name','created_at']),
			'latest_programs' => Program::orderBy('created_at', 'desc')->take(5)->get(['id','name','created_at']),

			'most_popular_file' => File::withCount('starredBy')->orderBy('starred_by_count', 'desc')->first(),
			'most_popular_tag' => Tag::withCount('files')->orderBy('files_count', 'desc')->first(),
			'most_popular_program' => Program::with(['users.files'])->get()->map(function ($program) {
				$program->files_count = $program->users->sum(function ($user) {
					return $user->files->count();
				});
				return $program;
			})->sortByDesc('files_count')->first(),

			'total_storage_used_mb' => round(File::sum('size') / 1024 / 1024, 2),
			'average_file_size_kb' => round(File::avg('size') / 1024, 2),
		];

		return Inertia::render('Statistics/Index', [
			'statistics' => $statistics,
		]);
	}
}
