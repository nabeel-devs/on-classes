<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive',
            'is_discounted' => 'boolean',
            'discount_code' => 'nullable|string|max:50',
            'discount_percentage' => 'nullable|integer|min:1|max:100',
            'cover_image' => 'nullable|image|max:2048',
            'detail_images.*' => 'nullable|image|max:2048',
            'source_file' => 'nullable|mimes:mp3,mp4,zip,rar,png,jpg,jpeg|max:20480',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => Auth::id(),
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->is_discounted) {
                if (!$this->discount_code || !$this->discount_percentage) {
                    $validator->errors()->add('discount_code', 'Discount code is required when the product is discounted.');
                    $validator->errors()->add('discount_percentage', 'Discount percentage is required when the product is discounted.');
                }
            }
        });
    }
}
