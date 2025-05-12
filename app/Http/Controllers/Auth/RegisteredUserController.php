<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register', [
            'programs' => \App\Models\Program::select('id', 'name', 'code')->orderBy('name')->get()
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'program_id' => 'required|exists:programs,id',
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
                'regex:/^[a-zA-Z0-9._%+-]+@bpsu\.edu\.ph$/',
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_role' => ['required', 'in:student,faculty'],
        ], [
            'email.regex' => 'Only @bpsu.edu.ph email addresses are allowed.',
            'program_id.required' => 'Please select a program.',
            'program_id.exists' => 'The selected program is invalid.',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'program_id' => $request->program_id,
            'password' => Hash::make($request->password),
            'user_role' => $request->user_role,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return to_route('home')->with('success', 'Registration successful! Welcome!');
    }
}
