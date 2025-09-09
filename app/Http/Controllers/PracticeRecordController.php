<?php

namespace App\Http\Controllers;

use App\Models\PracticeRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PracticeRecordController extends Controller
{
	use AuthorizesRequests;
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

		PracticeRecord::create($validated);

		return response()->json(['message' => 'Practice record saved successfully.'], 201);
	}
}
