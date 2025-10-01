<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VerificationController extends Controller
{
    /**
     * Show the document upload page
     */
    public function showUpload()
    {
        $user = Auth::user();
        
        // Redirect if user doesn't need to upload document
        if (!$user->needsDocumentUpload() && !$user->isVerificationRejected()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Verification/Upload', [
            'user' => $user,
            'documentRequired' => $user->user_role === 'student' ? 'COR (Certificate of Registration)' : 'Valid ID'
        ]);
    }

    /**
     * Handle document upload
     */
    public function uploadDocument(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'document' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120' // 5MB max
            ]
        ]);

        // Delete old document if exists
        if ($user->verification_document_path) {
            Storage::disk('public')->delete($user->verification_document_path);
        }

        // Store new document
        $path = $request->file('document')->store('verification-documents', 'public');

        // Update user record
        $user->update([
            'verification_document_path' => $path,
            'verification_status' => 'pending',
            'document_uploaded_at' => now(),
            'verification_notes' => null, // Clear previous notes
            'verified_at' => null,
            'verified_by' => null
        ]);

        return redirect()->back()->with('success', 'Document uploaded successfully! Please wait for admin approval.');
    }

    /**
     * Show verification status page
     */
    public function showStatus()
    {
        $user = Auth::user();
        
        return Inertia::render('Verification/Status', [
            'user' => $user->load('verifiedBy')
        ]);
    }
}
