<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestoreVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'confirmation' => 'required|accepted',
            'notes' => 'nullable|string|max:500',
            'create_new_version' => 'sometimes|boolean',
            'preserve_current' => 'sometimes|boolean',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'create_new_version' => filter_var($this->create_new_version ?? true, FILTER_VALIDATE_BOOLEAN),
            'preserve_current' => filter_var($this->preserve_current ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}