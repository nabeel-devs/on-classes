<?php

namespace App\Http\Requests\user;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
    public function rules()
    {
        return [
            'email' => [
                'required',
                'string',
                'max:255',
            ],
            'password' => 'required|string|min:8|max:255',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'The email or username field is required.',
            'email.string' => 'The email or username must be a valid string.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 6 characters.',
        ];
    }
}
