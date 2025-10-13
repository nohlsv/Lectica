<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Notifications\FileDeniedNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FileVerificationController extends Controller
{
	use AuthorizesRequests;

	/**
	 * Display a list of unverified files.
	 */
	public function index(Request $request)
	{
		$this->authorize('verify', File::class);

		$user = auth()->user();
		
		$query = File::where('verified', false)
			->where('is_denied', false)
			->with(['user', 'user.program']);

		// Apply college filtering logic
		if ($user->isAdmin()) {
			// Admin users can see and filter all files
			$collegeFilter = $request->get('college', 'my_college');
			$showAllColleges = $request->boolean('show_all_colleges', false);

			if ($user->college && $collegeFilter === 'my_college' && !$showAllColleges) {
				// Show only files from admin's college
				$query->whereHas('user.program', function ($q) use ($user) {
					$q->where('college', $user->college);
				});
			} elseif ($collegeFilter !== 'all' && $collegeFilter !== 'my_college') {
				// Show files from specific college
				$query->whereHas('user.program', function ($q) use ($collegeFilter) {
					$q->where('college', $collegeFilter);
				});
			}
			// If $showAllColleges is true or $collegeFilter is 'all', no additional filtering (show all colleges)
		} elseif ($user->isFaculty()) {
			// Faculty users can only see files from their college (no filtering options)
			if ($user->college) {
				$query->whereHas('user.program', function ($q) use ($user) {
					$q->where('college', $user->college);
				});
			}
			// If faculty has no college, they see no files (security measure)
			else {
				$query->whereRaw('1 = 0'); // Return no results
			}
		}

		$files = $query->paginate(10)->withQueryString();

		// Prepare response data
		$responseData = [
			'files' => $files,
			'is_admin' => $user->isAdmin(),
			'user_college' => $user->college, // Always provide user college for display
		];

		if ($user->isAdmin()) {
			// Get all available colleges for filter dropdown (admin only)
			$availableColleges = \App\Models\Program::distinct()
				->whereNotNull('college')
				->pluck('college')
				->sort()
				->values();

			$responseData['filters'] = [
				'college' => $request->get('college', 'my_college'),
				'show_all_colleges' => $request->boolean('show_all_colleges', false),
			];
			$responseData['available_colleges'] = $availableColleges;
		}

		return Inertia::render('Files/Verify', $responseData);
	}

	/**
	 * Verify a specific file.
	 */
	public function verify(File $file)
	{
		$this->authorize('verify', $file);

		$file->update([
			'verified' => true,
			'verified_at' => now(),
			'verified_by' => auth()->id()
		]);

		// Send verification notification to the file owner
		try {
			$file->user->notify(new \App\Notifications\FileVerifiedNotification($file));
		} catch (\Exception $e) {
			\Log::error('Failed to send file verification notification', [
				'file_id' => $file->id,
				'error' => $e->getMessage()
			]);
		}

		return back();
	}

	/**
	 * Deny a specific file.
	 */
	public function deny(Request $request, File $file)
	{
		$this->authorize('verify', $file);

		$request->validate([
			'denial_reason' => 'required|string|max:1000'
		]);

		$file->update([
			'is_denied' => true,
			'denial_reason' => $request->denial_reason,
			'denied_at' => now(),
			'verified_by' => auth()->id()
		]);

		// Send notification to the file owner
		$file->user->notify(new FileDeniedNotification($file, $request->denial_reason));

		// Mark as notified
		$file->update(['user_notified_of_denial' => true]);

		return back();
	}
}
