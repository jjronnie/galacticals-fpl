<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolveProfileVerificationSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:approve,reject'],
            'rejection_reason' => ['nullable', 'required_if:action,reject', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required_if' => 'Please provide a reason so the user can retry verification.',
        ];
    }
}
