<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacherId = $this->route('teacher')?->id;
        $userId = $this->route('teacher')?->user_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'min:8',
            ],
            'boarding_school_id' => ['nullable', 'exists:boarding_schools,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'nip' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('teachers', 'nip')->ignore($teacherId),
            ],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pegawai wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'position_id.required' => 'Jabatan wajib dipilih.',
            'position_id.exists' => 'Jabatan tidak valid.',
            'nip.unique' => 'NIP sudah terdaftar.',
        ];
    }
}
