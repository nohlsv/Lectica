<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsFullyVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // If user hasn't verified email, redirect to email verification
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // If user needs to upload document, redirect to document upload
        if ($user->needsDocumentUpload() || $user->isVerificationRejected()) {
            return redirect()->route('verification.upload');
        }

        // If user has document pending approval, show status page
        if ($user->hasDocumentPendingApproval()) {
            return redirect()->route('verification.status');
        }

        // If user verification was rejected, redirect to upload page
        if ($user->isVerificationRejected()) {
            return redirect()->route('verification.upload');
        }

        // User is fully verified, proceed
        return $next($request);
    }
}
