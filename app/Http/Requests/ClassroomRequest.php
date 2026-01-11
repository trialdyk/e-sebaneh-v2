<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassroomRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'string', 'max:50'], // e.g., '1', '2', '3', 'IX', 'X'
            'school_year_id' => ['required', 'exists:school_years,id'],
            'teacher_id' => ['required', 'exists:users,id'], // ID of the user who is a teacher
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Kelas',
            'level' => 'Tingkat',
            'school_year_id' => 'Tahun Ajaran',
            'teacher_id' => 'Wali Kelas',
        ];
    }
}
