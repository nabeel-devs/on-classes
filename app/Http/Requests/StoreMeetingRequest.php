<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreMeetingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true; // Set to true if you don't need authorization logic
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'topic' => 'required|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'description' => 'required|string|max:500',
            'livestream' => 'nullable|boolean',
            'meet_link' => 'nullable|string',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => Auth::id(),
        ]);
    }
}
