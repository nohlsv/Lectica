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
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'user_role' => ['required', 'in:student,faculty'],
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
        ];

        // Add conditional validation rules for students
        if ($request->user_role === 'student') {
            $rules['program_id'] = [
                'required',
                'integer',
                'exists:programs,id',
            ];
            $rules['year_of_study'] = [
                'required',
                'string',
                'in:1st Year,2nd Year,3rd Year,4th Year,5th Year,Graduate',
            ];
        }

        $request->validate($rules, [
            'email.regex' => 'Only @bpsu.edu.ph email addresses are allowed.',
            'program_id.required' => 'Please select a program.',
            'program_id.exists' => 'The selected program is invalid.',
            'year_of_study.required' => 'Year of study is required for students.',
            'year_of_study.in' => 'Invalid year of study selected.',
            'user_role.in' => 'Invalid user role selected.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        $userData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_role' => $request->user_role,
        ];

        if ($request->user_role === 'student') {
            $userData['program_id'] = $request->program_id;
            $userData['year_of_study'] = $request->year_of_study;
        }

        $user = User::create($userData);

        event(new Registered($user));

        Auth::login($user);

        return to_route('verification.notice')->with('success', 'Registration successful! Please verify your email first.');
    }
}
