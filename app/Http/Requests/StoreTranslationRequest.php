<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string|max:255',
            'locale' => 'required|string|max:10',
            'value' => 'required|string',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'The translation key is required.',
            'key.max' => 'The translation key cannot exceed 255 characters.',
            'locale.required' => 'The locale is required.',
            'locale.max' => 'The locale cannot exceed 10 characters.',
            'value.required' => 'The translation value is required.',
            'tags.array' => 'Tags must be an array.',
            'tags.*.string' => 'Each tag must be a string.',
            'tags.*.max' => 'Each tag cannot exceed 50 characters.',
        ];
    }
}
