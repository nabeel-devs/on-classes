<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeetingRequest extends FormRequest
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
            'topic' => 'sometimes|required|string|max:255',
            'date' => 'sometimes|required|date',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
            'description' => 'sometimes|required|string|max:500',
            'livestream' => 'nullable|boolean',
        ];
    }
}
