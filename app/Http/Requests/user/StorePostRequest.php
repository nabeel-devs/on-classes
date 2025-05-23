<?php

namespace App\Http\Requests\user;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
            'content' => 'required|string',
            'type' => 'required|in:text,image,video,product,course,reel',
            'who_can_reply' => 'required|in:everyone,verified_accounts,only_community',
            'who_can_see' => 'required|in:everyone,verified_accounts,only_community,subscribers',
            'scheduled_at' => 'nullable|date',
            'is_story' => 'required',
            'media' => 'nullable|file|mimes:jpeg,png,jpg,mp4|max:20480',
            'audio' => 'nullable|file|max:5120',
            'is_poll' => 'nullable|boolean',
            'poll_options' => 'required_if:is_poll,true|array|min:2|max:10',
            'poll_options.*' => 'required|string|max:100',
            'poll_end_at' => 'nullable|date|after:now',
            'commenting_enabled' => 'nullable|boolean',
        ];
    }
}
