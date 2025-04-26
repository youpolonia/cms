<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduledExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'frequency' => 'sometimes|in:daily,weekly,monthly',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'sometimes|in:active,paused,completed',
            'anonymize' => 'sometimes|boolean',
            'anonymization_options' => 'nullable|array',
            'export_params' => 'sometimes|array'
        ];
    }
}