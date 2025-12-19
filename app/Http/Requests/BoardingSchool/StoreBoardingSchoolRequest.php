<?php

namespace App\Http\Requests\BoardingSchool;

use Illuminate\Foundation\Http\FormRequest;

class StoreBoardingSchoolRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama pondok wajib diisi.',
            'name.max' => 'Nama pondok maksimal 255 karakter.',
            'address.required' => 'Alamat pondok wajib diisi.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format gambar harus jpeg, jpg, png, atau webp.',
            'photo.max' => 'Ukuran gambar maksimal 2MB.',
            'facilities.*.required' => 'Nama fasilitas wajib diisi.',
            'facilities.*.max' => 'Nama fasilitas maksimal 255 karakter.',
        ];
    }
}
