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
            'programs' => \App\Models\Program::orderBy('name')->get()
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
            // program_id is required only if user_role is student
            'program_id' => [
                'required_if:user_role,student',
                'integer',
                'exists:programs,id',
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
                'regex:/^[a-zA-Z0-9._%+-]+@bpsu\.edu\.ph$/',
            ],
            // Add year of study required only if user_role is student
            'year_of_study' => [
                'required_if:user_role,student',
                'string',
                'in:1st Year,2nd Year,3rd Year,4th Year,5th Year,Graduate',
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_role' => ['required', 'in:student,faculty'],
        ], [
            'email.regex' => 'Only @bpsu.edu.ph email addresses are allowed.',
            'program_id.required_if' => 'Please select a program.',
            'program_id.exists' => 'The selected program is invalid.',
            'year_of_study.required_if' => 'Year of study is required for students.',
            'year_of_study.in' => 'Invalid year of study selected.',
            'user_role.in' => 'Invalid user role selected.',
            'password.confirmed' => 'The password confirmation does not match.',
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
