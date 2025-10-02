<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $userId = $request->route('id');
        $hash = $request->route('hash');
        
        // Always validate the signature first
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired verification link.');
        }
        
        // Get user from URL parameters (not from auth)
        $user = User::findOrFail($userId);
        
        // Validate the hash matches the user's email
        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification hash.');
        }
        
        // If not authenticated, log the user in
        if (!Auth::check() || Auth::id() !== $user->id) {
            Auth::login($user);
        }
        
        if ($user->hasVerifiedEmail()) {
            // If email is already verified, check if they need document upload
            if ($user->needsDocumentUpload() || $user->isVerificationRejected()) {
                return redirect()->route('verification.upload');
            }
            return redirect()->intended(route('home', absolute: false).'?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // After email verification, redirect to document upload
        if ($user->needsDocumentUpload() || $user->isVerificationRejected()) {
            return redirect()->route('verification.upload')->with('success', 'Email verified! Please upload your verification document.');
        }

        return redirect()->intended(route('home', absolute: false).'?verified=1');
    }
}
