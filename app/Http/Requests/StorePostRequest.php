<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $title = $this->input('title');
        if ($title && !$this->filled('slug')) {
            $this->merge(['slug' => Str::slug($title)]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:4', 'max:200'],
            'slug' => ['required', 'string', 'max:220', 'unique:posts,slug'],
            'content' => ['required', 'string', 'min:20'],

            'status' => ['required', Rule::in(['draft', 'published', 'archived', 'default'])],
            'published_at' => ['nullable', 'date', 'required_if:status,published', 'before_or_equal:now'],
            'cover_image' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048']
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'El Titulo es requerido y obligatorio'
        ];
    }
}
