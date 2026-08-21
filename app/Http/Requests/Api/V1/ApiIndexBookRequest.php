<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ApiIndexBookRequest extends FormRequest
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
            'keyword' => ['nullable', 'string', 'max:20'],
            'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages()
    {
        return [
            'keyword.string' => '文字列で入力してください。',
            'keyword.max' => 'キーワードは20文字以内で入力してください。',
            'genre_id.integer' => 'ジャンルは数値で入力してください。',
            'genre_id.exists' => 'このジャンルは存在しません。',
            'page.integer' => 'ページ番号は数値で入力してください。',
            'page.min' => 'ページ番号は1以上で入力してください。',
            'per_page.integer' => '1ページの件数は数値で入力してください。',
            'per_page.min' => '1ページの件数は1以上を入力してください。',
            'per_page.max' => '1ページの件数は50以内で入力してください。',
        ];
    }
}
