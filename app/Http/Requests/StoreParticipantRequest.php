<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreParticipantRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id', // Ensure the user exists
        ];
    }
}
