<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string',
            'change_description' => 'required|string|max:255',
            'is_autosave' => 'boolean',
            'branch_name' => 'nullable|string|max:100',
            'parent_version_id' => 'nullable|exists:content_versions,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
        ];
    }
}