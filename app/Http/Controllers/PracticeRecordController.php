<?php

namespace App\Http\Controllers;

use App\Models\PracticeRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Services\QuestService;
use App\Traits\TracksStudyActivity;

class PracticeRecordController extends Controller
{
	use AuthorizesRequests, TracksStudyActivity;
	public function __construct(private QuestService $questService) {}
	public function index(): Response
	{
		$userId = auth()->id();
		// Get all practice records for the user, grouped by file (quiz)
		$records = PracticeRecord::with(['file'])
			->where('user_id', $userId)
			->orderByDesc('created_at')
			->get();

		// Group records by file_id
		$grouped = $records->groupBy('file_id')->map(function ($group) {
			return [
				'file' => $group->first()->file,
				'attempts' => $group->map(function ($record) {
					return [
						'id' => $record->id,
						'type' => $record->type,
						'correct_answers' => $record->correct_answers,
						'total_questions' => $record->total_questions,
						'created_at' => $record->created_at,
						'mistakes' => $record->mistakes,
					];
				}),
			];
		})->values();

		return Inertia::render('PracticeRecords/Index', [
			'groupedRecords' => $grouped,
		]);
	}

	public function show(PracticeRecord $practiceRecord): Response
	{
		$this->authorize('view', $practiceRecord);

		// Fetch all previous records for the same file and user, ordered by creation date
		$progressRecords = PracticeRecord::where('file_id', $practiceRecord->file_id)
			->where('user_id', $practiceRecord->user_id)
			->orderBy('created_at')
			->get(['id', 'created_at', 'correct_answers', 'total_questions']);

		return Inertia::render('PracticeRecords/Show', [
			'record' => $practiceRecord->load('file'),
			'progressRecords' => $progressRecords,
		]);
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'file_id' => 'required|exists:files,id',
			'type' => 'required|string|in:flashcard,quiz',
			'correct_answers' => 'required|integer|min:0',
			'total_questions' => 'required|integer|min:1',
			'mistakes' => 'nullable|array',
		]);

		$validated['user_id'] = auth()->id();

		// Encode mistakes as JSON
		if (isset($validated['mistakes'])) {
			$validated['mistakes'] = json_encode($validated['mistakes']);
		}

		$record = PracticeRecord::create($validated);

		// Update quest progress, award XP, and track study activity
		$user = auth()->user();
		
		if ($validated['type'] === 'quiz') {
			$this->questService->updateQuestProgress($user, 'practice_quiz');
			
			// Award XP for quiz completion
			$xpEarned = $validated['correct_answers'] * 10; // 10 XP per correct answer
			$user->addExperience($xpEarned);
			
			// Track quiz activity for streaks
			$this->recordQuizActivity($user->id, [
				'questions_answered' => $validated['total_questions'],
				'correct_answers' => $validated['correct_answers'],
				'points_earned' => $xpEarned,
				'time_spent_minutes' => 5, // Estimate 5 minutes for quiz completion
			]);
		} elseif ($validated['type'] === 'flashcard') {
			$this->questService->updateQuestProgress($user, 'practice_flashcard');
			
			// Award XP for flashcard review (fewer points than quizzes)
			$xpEarned = $validated['total_questions'] * 2; // 2 XP per flashcard reviewed
			$user->addExperience($xpEarned);
			
			// Track flashcard activity for streaks
			$this->recordFlashcardActivity(
				$user->id, 
				$validated['total_questions'], // cards reviewed
				3, // Estimate 3 minutes for flashcard session
				$xpEarned // XP earned for flashcards
			);
		}

		return response()->json(['message' => 'Practice record saved successfully.'], 201);
	}
}
