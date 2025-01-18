<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Update this if you need authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'total' => 'required|numeric',
            'fee' => 'nullable|numeric',
            'status' => 'nullable|string',
            'items' => 'required|array',
            'items.*.course_id' => 'required|exists:courses,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'payment_method' => 'required|string',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => Auth::id(),
        ]);
    }
}
