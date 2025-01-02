<?php

namespace App\Http\Requests\course;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class CourseStoreRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_free' => 'nullable|boolean',
            'allow_download' => 'nullable|boolean',
            'strict_flow' => 'nullable|boolean',
            'thumbnail' => 'nullable|file|mimes:jpeg,png,jpg|max:5120',
            'is_discounted' => 'boolean',
            'discount_code' => 'nullable|string|max:50',
            'discount_percentage' => 'nullable|integer|min:1|max:100',
            'who_has_access' => 'nullable',
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
                    $validator->errors()->add('discount_code', 'Discount code is required when the course is discounted.');
                    $validator->errors()->add('discount_percentage', 'Discount percentage is required when the course is discounted.');
                }
            }
        });
    }

}
