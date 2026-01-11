<?php

namespace App\Imports;

use App\Models\Position;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;

class TeachersImport implements
    ToCollection,
    WithHeadingRow,
    SkipsOnError,
    SkipsOnFailure
{
    use Importable;

    protected $failures = [];
    protected $errors = [];

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $user = auth()->user();
        $boardingSchoolId = $user->hasRole('admin-pondok')
            ? $user->boardingSchools()->first()?->id
            : null;

        foreach ($rows as $index => $row) {
            try {
                // Get row number (skip header)
                $rowNumber = $index + 2;

                // Validate row data
                $validator = Validator::make($row->toArray(), [
                    'nip' => ['nullable', 'max:255', Rule::unique('teachers', 'nip')],
                    'nama_lengkap' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                    'no_hp' => ['nullable', 'string', 'max:20'],
                    'jabatan' => ['required', 'string'],
                ], [
                    'nip.unique' => 'NIP sudah terdaftar',
                    'nama_lengkap.required' => 'Nama lengkap wajib diisi',
                    'email.required' => 'Email wajib diisi',
                    'email.email' => 'Format email tidak valid',
                    'email.unique' => 'Email sudah terdaftar',
                    'jabatan.required' => 'Jabatan wajib diisi',
                ]);

                if ($validator->fails()) {
                    $this->failures[] = new Failure(
                        $rowNumber,
                        'validation',
                        $validator->errors()->all(),
                        $row->toArray()
                    );
                    continue;
                }

                // Find position by name
                $position = Position::where('name', $row['jabatan'])->first();
                if (!$position) {
                    $this->failures[] = new Failure(
                        $rowNumber,
                        'jabatan',
                        ["Jabatan '{$row['jabatan']}' tidak ditemukan dalam sistem"],
                        $row->toArray()
                    );
                    continue;
                }

                // Validate boarding school for admin-pondok
                if (!$boardingSchoolId) {
                    $this->failures[] = new Failure(
                        $rowNumber,
                        'boarding_school',
                        ['User tidak memiliki pondok yang terkait'],
                        $row->toArray()
                    );
                    continue;
                }

                DB::beginTransaction();

                try {
                    // Create user
                    $user = User::create([
                        'name' => $row['nama_lengkap'],
                        'email' => $row['email'],
                        'phone_number' => $row['no_hp'] ?? null,
                        'password' => Hash::make('password123'), // Default password
                        'gender' => 'male', // Default gender
                    ]);

                    // Assign role
                    $user->assignRole('teacher');

                    // Create teacher
                    Teacher::create([
                        'user_id' => $user->id,
                        'boarding_school_id' => $boardingSchoolId,
                        'position_id' => $position->id,
                        'nip' => $row['nip'] ?? null,
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->errors[] = $e;
                    $this->failures[] = new Failure(
                        $rowNumber,
                        'database',
                        ['Gagal menyimpan data: ' . $e->getMessage()],
                        $row->toArray()
                    );
                }
            } catch (\Exception $e) {
                $this->errors[] = $e;
            }
        }
    }

    public function onError(\Throwable $e)
    {
        $this->errors[] = $e;
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    public function failures(): Collection
    {
        return collect($this->failures);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
