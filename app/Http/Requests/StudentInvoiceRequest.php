<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentInvoiceRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'amount' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'type' => 'required|in:all_students,by_classroom,by_gender,specific_students',
            'for_gender' => 'required_if:type,by_gender|in:male,female',
            'classrooms' => 'required_if:type,by_classroom|array|min:1',
            'classrooms.*' => 'exists:classrooms,id',
            'students' => 'required_if:type,specific_students|array|min:1',
            'students.*' => 'exists:students,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama tagihan wajib diisi.',
            'amount.required' => 'Jumlah tagihan wajib diisi.',
            'amount.min' => 'Jumlah tagihan minimal Rp 1.',
            'type.required' => 'Tipe tagihan wajib dipilih.',
            'for_gender.required_if' => 'Gender wajib dipilih untuk tipe tagihan berdasarkan gender.',
            'classrooms.required_if' => 'Minimal 1 kelas harus dipilih untuk tipe tagihan per kelas.',
            'students.required_if' => 'Minimal 1 santri harus dipilih untuk tipe tagihan per santri tertentu.',
        ];
    }
}
