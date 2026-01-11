<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'icon' => 'required|string|max:100',
            'description' => 'required|string|max:1000',
            'content' => 'nullable|string',
            'image' => $this->isMethod('POST')
                ? 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048'
                : 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul program wajib diisi',
            'icon.required' => 'Icon wajib dipilih',
            'description.required' => 'Deskripsi program wajib diisi',
            'order.required' => 'Urutan wajib diisi',
        ];
    }
}
