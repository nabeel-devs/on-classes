<?php

namespace App\Http\Requests\course;

use Illuminate\Foundation\Http\FormRequest;

class LessonStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'course_id' => 'required|exists:courses,id',
            'module_id' => 'required|exists:modules,id',
            'video' => 'nullable|file|mimes:mp4,mkv,webm|max:20480',
            'thumbnail' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
        ];
    }
}
