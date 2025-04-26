<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'change_description' => 'sometimes|string|max:255',
            'branch_name' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
            'approval_status' => ['sometimes', Rule::in(['pending', 'approved', 'rejected'])],
            'rejection_reason' => 'required_if:approval_status,rejected|string|max:500',
        ];
    }
}