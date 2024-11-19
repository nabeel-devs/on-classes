<?php

namespace App\Http\Requests\user;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
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
            'content' => 'nullable|string',
            'type' => 'nullable|in:text,image,video,product,course',
            'who_can_reply' => 'nullable|in:everyone,verified_accounts,only_community',
            'scheduled_at' => 'nullable|date',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,mp4|max:20480',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}
