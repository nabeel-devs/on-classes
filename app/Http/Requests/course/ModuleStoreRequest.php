<?php

namespace App\Http\Requests\course;

use Illuminate\Foundation\Http\FormRequest;

class ModuleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true; // Adjust authorization logic as needed
    }

    public function rules()
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'availability_after_weeks' => 'nullable|integer|min:0',
            'video' => 'nullable|file|mimes:mp4,mkv,webm|max:20480',
            'thumbnail' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
        ];
    }
}
