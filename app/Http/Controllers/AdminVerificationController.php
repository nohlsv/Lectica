<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminVerificationController extends Controller
{
    private function ensureAdmin()
    {
        if (!Auth::user() || Auth::user()->user_role !== 'admin') {
            abort(403, 'Access denied. Admin privileges required.');
        }
    }

    /**
     * Show pending verifications
     */
    public function index()
    {
        $this->ensureAdmin();
        $pendingUsers = User::where('verification_status', 'pending')
            ->whereNotNull('verification_document_path')
            ->with(['program', 'verifiedBy'])
            ->orderBy('document_uploaded_at', 'desc')
            ->get();

        return Inertia::render('Admin/PendingVerifications', [
            'pendingUsers' => $pendingUsers
        ]);
    }

    /**
     * Show specific user's verification details
     */
    public function show(User $user)
    {
        $this->ensureAdmin();
        
        $user->load(['program', 'verifiedBy']);
        
        return Inertia::render('Admin/VerificationDetail', [
            'user' => $user,
            'programs' => \App\Models\Program::orderBy('name')->get(),
            'documentUrl' => $user->verification_document_url,
            'isDocumentImage' => $user->is_verification_document_image
        ]);
    }

    /**
     * Approve user verification
     */
    public function approve(Request $request, User $user)
    {
        $this->ensureAdmin();
        
        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        $user->update([
            'verification_status' => 'approved',
            'verification_notes' => $request->notes,
            'verified_at' => now(),
            'verified_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'User verification approved successfully.');
    }

    /**
     * Reject user verification
     */
    public function reject(Request $request, User $user)
    {
        $this->ensureAdmin();
        
        $request->validate([
            'notes' => 'required|string|max:500'
        ]);

        $user->update([
            'verification_status' => 'rejected',
            'verification_notes' => $request->notes,
            'verified_at' => now(),
            'verified_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'User verification rejected.');
    }

    /**
     * Update user details during verification
     */
    public function updateUserDetails(Request $request, User $user)
    {
        $this->ensureAdmin();
        
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
                'regex:/^[a-zA-Z0-9._%+-]+@bpsu\.edu\.ph$/',
            ],
        ];

        if ($user->user_role === 'student') {
            $rules['program_id'] = 'required|integer|exists:programs,id';
            $rules['year_of_study'] = 'required|string|in:1st Year,2nd Year,3rd Year,4th Year,5th Year,Graduate';
        }

        $validated = $request->validate($rules);

        $user->update($validated);

        return redirect()->back()->with('success', 'User details updated successfully.');
    }

    /**
     * Show all verifications (approved, rejected, pending)
     */
    public function allVerifications()
    {
        $this->ensureAdmin();
        
        $users = User::whereIn('verification_status', ['pending', 'approved', 'rejected'])
            ->with(['program', 'verifiedBy'])
            ->orderByRaw('document_uploaded_at IS NULL, document_uploaded_at DESC')
            ->paginate(20);

        // Ensure the relationship data is properly loaded by transforming the pagination result
        $users->through(function ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'user_role' => $user->user_role,
                'verification_status' => $user->verification_status,
                'verification_notes' => $user->verification_notes,
                'document_uploaded_at' => $user->document_uploaded_at,
                'verified_at' => $user->verified_at,
                'verified_by' => $user->verified_by,
                'verifiedBy' => $user->verifiedBy ? [
                    'id' => $user->verifiedBy->id,
                    'first_name' => $user->verifiedBy->first_name,
                    'last_name' => $user->verifiedBy->last_name,
                ] : null,
                'program' => $user->program ? [
                    'id' => $user->program->id,
                    'name' => $user->program->name,
                ] : null,
            ];
        });

        return Inertia::render('Admin/AllVerifications', [
            'users' => $users
        ]);
    }
}
