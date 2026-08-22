<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
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
            'title' =>  ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'regex:/^\d{10}$|^\d{13}$/', Rule::unique('books', 'isbn')->ignore($this->book->id)],
            'published_date' => ['nullable', 'date'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'image_url' => ['nullable', 'url'],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルの入力は必須です。',
            'title.string' => '文字列で入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'author.required' => '著者名の入力は必須です。',
            'author.string' => '文字列で入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',
            'isbn.string' => '文字列で入力してください。',
            'isbn.regex' => '10桁か13桁の数字を入力してください。',
            'isbn.unique' => 'このISBNは重複しています。',
            'published_date.date' => '有効な日付を選択してください。',
            'genres.required' => 'ジャンルは選択必須です。',
            'genres.array' => 'ジャンルの形式が正しくありません。',
            'genres.min' => 'ジャンルは選択必須です。',
            'genres.*.exists' => 'このジャンルは存在しません。',
            'description.string' => '文字列で入力してください。',
            'description.max' => '説明は500文字で入力してください。',
            'image_url.url' => 'URL形式で入力してください。',
        ];
    }
}