<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'translations' => 'required|array',
            'translations.*' => 'required|array',
            'language_code' => [
                'required',
                'string',
                Rule::in(array_keys(config('app.enabled_languages')))
            ],
            'content' => 'required|array'
        ];
    }

    public function messages()
    {
        return [
            'language_code.in' => 'The selected language is not enabled.',
            'translations.required' => 'Translations data is required.',
            'content.required' => 'Content data is required.'
        ];
    }
}