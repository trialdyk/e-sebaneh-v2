<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:100'],
            'quote' => ['required', 'string'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];

        // Photo is required on create, optional on update
        if ($this->isMethod('POST')) {
            $rules['photo'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
        } else {
            $rules['photo'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'role.required' => 'Role/Jabatan wajib diisi',
            'quote.required' => 'Testimoni wajib diisi',
            'photo.image' => 'File harus berupa gambar',
            'photo.mimes' => 'Format gambar harus: jpg, jpeg, png, atau webp',
            'photo.max' => 'Ukuran gambar maksimal 2MB',
            'rating.min' => 'Rating minimal 1',
            'rating.max' => 'Rating maksimal 5',
        ];
    }
}
