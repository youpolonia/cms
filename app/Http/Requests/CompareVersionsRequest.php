<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompareVersionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'version1_id' => 'required|exists:content_versions,id',
            'version2_id' => 'required|exists:content_versions,id|different:version1_id',
            'granularity' => ['sometimes', Rule::in(['line', 'word', 'character'])],
            'include_metadata' => 'sometimes|boolean',
            'highlight_changes' => 'sometimes|boolean',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'granularity' => $this->granularity ?? 'line',
            'include_metadata' => filter_var($this->include_metadata ?? true, FILTER_VALIDATE_BOOLEAN),
            'highlight_changes' => filter_var($this->highlight_changes ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}