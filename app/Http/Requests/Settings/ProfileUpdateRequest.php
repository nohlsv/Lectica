<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
                'regex:/^[a-zA-Z0-9._%+-]+@bpsu\.edu\.ph$/',
            ],
            'program_id' => ['required', 'exists:programs,id'],
            'year_of_study' => ['required', 'string', 'in:1st Year,2nd Year,3rd Year,4th Year,5th Year,Graduate'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.regex' => 'Only @bpsu.edu.ph email addresses are allowed.',
            'program_id.required' => 'Please select a program.',
            'program_id.exists' => 'The selected program is invalid.',
            'year_of_study.required' => 'Please select your year level.',
            'year_of_study.in' => 'The selected year level is invalid.',
        ];
    }
}
