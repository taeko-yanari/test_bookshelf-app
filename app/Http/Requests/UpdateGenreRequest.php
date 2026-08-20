<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGenreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:20', Rule::unique('genres', 'name')->ignore($this->route('genre'))]
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'ジャンルの入力は必須です。',
            'name.string' => '文字列で入力してください。',
            'name.max' => 'ジャンルは20文字以内で入力してください。',
            'name.unique' => 'このジャンル名は重複しています。',
        ];
    }
}