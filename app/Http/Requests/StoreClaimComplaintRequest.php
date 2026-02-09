<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClaimComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Please add a short subject for this complaint.',
            'message.required' => 'Please explain the complaint details.',
            'message.min' => 'Please provide a bit more detail so the admin can investigate.',
        ];
    }
}
