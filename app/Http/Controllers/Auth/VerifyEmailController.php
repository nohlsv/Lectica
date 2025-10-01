<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        
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
