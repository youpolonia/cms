<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content_id' => 'required|exists:contents,id',
            'action' => ['required', Rule::in(['approve', 'reject', 'request_changes'])],
            'reason' => 'required_if:action,reject,request_changes|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'notify_author' => 'sometimes|boolean',
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high'])],
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'notify_author' => filter_var($this->notify_author ?? true, FILTER_VALIDATE_BOOLEAN),
            'priority' => $this->priority ?? 'medium',
        ]);
    }
}