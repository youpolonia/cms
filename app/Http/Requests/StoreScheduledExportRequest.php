<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduledExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'frequency' => 'required|in:daily,weekly,monthly',
            'start_date' => 'required|date|after:now',
            'end_date' => 'nullable|date|after:start_date',
            'anonymize' => 'boolean',
            'anonymization_options' => 'nullable|array',
            'export_params' => 'required|array'
        ];
    }
}