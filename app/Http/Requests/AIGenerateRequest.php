<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AIGenerateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'prompt' => 'required|string|min:10|max:1000',
            'tone' => 'sometimes|string|in:professional,casual,enthusiastic,friendly',
            'length' => 'sometimes|string|in:short,medium,long'
        ];
    }

    public function messages()
    {
        return [
            'prompt.required' => 'A prompt is required to generate content',
            'prompt.min' => 'Prompt must be at least 10 characters',
            'prompt.max' => 'Prompt cannot exceed 1000 characters',
            'tone.in' => 'Invalid tone selected',
            'length.in' => 'Invalid length selected'
        ];
    }
}