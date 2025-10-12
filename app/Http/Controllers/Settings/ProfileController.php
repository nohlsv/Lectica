<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'programs' => \App\Models\Program::select('id', 'name', 'code')->orderBy('name')->get(),
            'yearLevels' => [
                '1st Year',
                '2nd Year',
                '3rd Year',
                '4th Year',
                '5th Year',
                'Graduate',
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit')->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Archive all files owned by the user using the same method as FileController
        $adminUser = \App\Models\User::where('user_role', 'admin')->first();
        
        if ($adminUser) {
            $originalOwner = $user->first_name . " " . $user->last_name;
            $archiveDate = now()->format('Y-m-d H:i:s');
            
            \App\Models\File::where('user_id', $user->id)->get()->each(function ($file) use ($adminUser, $originalOwner, $archiveDate) {
                $archiveInfo = "\n\n[Originally owned by: " . $originalOwner . " - Archived on " . $archiveDate . "]";
                
                $file->update([
                    'user_id' => $adminUser->id,
                    'name' => '[ARCHIVED] ' . $file->name,
                    'description' => ($file->description ?? '') . $archiveInfo
                ]);
            });
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Profile deleted successfully!');
    }
}
