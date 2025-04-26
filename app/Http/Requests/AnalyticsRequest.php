<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'timeframe' => 'sometimes|string|in:hour,day,week,month,year',
            'workflow_id' => 'sometimes|exists:approval_workflows,id',
            'content_type' => 'sometimes|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'filters' => 'sometimes|array',
            'filters.*' => 'sometimes|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'timeframe.in' => 'The timeframe must be one of: hour, day, week, month, year',
            'workflow_id.exists' => 'The selected workflow does not exist',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date',
        ];
    }
}
