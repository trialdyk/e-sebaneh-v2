<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => $this->isMethod('POST')
                ? 'required|image|mimes:jpeg,jpg,png,webp|max:2048'
                : 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'category' => 'nullable|string|max:50',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul galeri wajib diisi',
            'image.required' => 'Gambar wajib diupload',
            'image.image' => 'File harus berupa gambar',
            'image.max' => 'Ukuran gambar maksimal 2MB',
            'order.required' => 'Urutan wajib diisi',
        ];
    }
}
