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

		// Filter by college if the user is faculty (not admin)
		if ($user->isFaculty() && $user->college) {
			$query->whereHas('user.program', function ($q) use ($user) {
				$q->where('college', $user->college);
			});
		}

		$files = $query->paginate(10)->withQueryString();

		return Inertia::render('Files/Verify', [
			'files' => $files,
		]);
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
