<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FileVerificationController extends Controller
{
	use AuthorizesRequests;

	/**
	 * Display a list of unverified files.
	 */
	public function index()
	{
		$this->authorize('verify', File::class);

		$files = File::where('verified', false)->with('user')->paginate(10);

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

		$file->update(['verified' => true]);

		return redirect()->route('files.verify')->with('success', 'File verified successfully!');
	}
}
