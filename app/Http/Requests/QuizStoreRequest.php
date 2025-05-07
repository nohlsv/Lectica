<?php

namespace App\Http\Requests;

use App\Enums\QuizType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class QuizStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'question' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(QuizType::class)],
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'string'],
        ];

        // Add conditional validation based on quiz type
        if ($this->type === QuizType::MULTIPLE_CHOICE->value) {
            $rules['options'] = ['required', 'array', 'min:2'];
            $rules['options.*'] = ['required', 'string'];
        } elseif ($this->type === QuizType::TRUE_FALSE->value) {
            $rules['answers'] = ['required', 'array', 'size:1'];
            $rules['answers.0'] = ['required', 'string', 'in:true,false'];
        }

        return $rules;
    }
}
