<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $postId = $this->route('post')?->id;

        return [
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'image' => $this->isMethod('POST')
                ? 'required|image|mimes:jpeg,jpg,png,webp|max:2048'
                : 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'category' => 'required|string|max:50',
            'published_at' => 'nullable|date',
            'is_published' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul berita wajib diisi',
            'excerpt.required' => 'Ringkasan berita wajib diisi',
            'content.required' => 'Konten berita wajib diisi',
            'image.required' => 'Gambar berita wajib diupload',
            'image.image' => 'File harus berupa gambar',
            'image.max' => 'Ukuran gambar maksimal 2MB',
            'category.required' => 'Kategori wajib dipilih',
        ];
    }
}
