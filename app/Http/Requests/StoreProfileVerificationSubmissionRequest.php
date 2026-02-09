<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfileVerificationSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'screenshot' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'screenshot.required' => 'Please upload a screenshot from the official FPL app.',
            'screenshot.image' => 'The uploaded file must be an image.',
            'screenshot.mimes' => 'Use JPG, PNG, or WEBP image formats for the screenshot.',
            'screenshot.max' => 'Screenshot size should be 5MB or less.',
        ];
    }
}
