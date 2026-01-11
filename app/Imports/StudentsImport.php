<?php

namespace App\Imports;

use App\Enums\GenderEnum;
use App\Enums\StudentStatusEnum;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;

class StudentsImport implements
    ToCollection,
    WithHeadingRow,
    SkipsOnError,
    SkipsOnFailure
{
    use Importable;

    protected $failures = [];
    protected $errors = [];

    /**
     * Convert gender input to database format
     */
    protected function convertGender(?string $input): ?string
    {
        if (!$input) return null;
        
        $input = Str::lower(trim($input));
        
        $genderMap = [
            'l' => 'male',
            'laki-laki' => 'male',
            'laki' => 'male',
            'male' => 'male',
            'pria' => 'male',
            'cowok' => 'male',
            'p' => 'female',
            'perempuan' => 'female',
            'female' => 'female',
            'wanita' => 'female',
            'cewek' => 'female',
        ];
        
        return $genderMap[$input] ?? null;
    }

    /**
     * Convert status input to database format
     */
    protected function convertStatus(?string $input): ?string
    {
        if (!$input) return 'active'; // Default to active
        
        $input = Str::lower(trim($input));
        
        $statusMap = [
            'aktif' => 'active',
            'active' => 'active',
            'tidak aktif' => 'inactive',
            'inactive' => 'inactive',
            'nonaktif' => 'inactive',
            'non aktif' => 'inactive',
            'lulus' => 'graduated',
            'graduated' => 'graduated',
            'alumni' => 'graduated',
            'keluar' => 'dropped_out',
            'do' => 'dropped_out',
            'dropped_out' => 'dropped_out',
            'keluar/do' => 'dropped_out',
        ];
        
        return $statusMap[$input] ?? 'active';
    }

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

                // Skip empty rows
                if (!$row['nis'] && !$row['nama_lengkap']) {
                    continue;
                }

                // Convert gender and status
                $gender = $this->convertGender($row['lp'] ?? $row['gender'] ?? null);
                $status = $this->convertStatus($row['status'] ?? null);

                // Validate gender conversion
                if (!$gender) {
                    $this->failures[] = new Failure(
                        $rowNumber,
                        'lp',
                        ["L/P tidak valid. Gunakan: L, Laki-laki, P, atau Perempuan"],
                        $row->toArray()
                    );
                    continue;
                }

                // Validate status conversion
                if (!in_array($status, ['active', 'inactive', 'graduated', 'dropped_out'])) {
                    $this->failures[] = new Failure(
                        $rowNumber,
                        'status',
                        ["Status tidak valid. Gunakan: Aktif, Tidak Aktif, Lulus, atau Keluar/DO"],
                        $row->toArray()
                    );
                    continue;
                }

                // Validate row data
                $validator = Validator::make([
                    'nis' => $row['nis'],
                    'nama_lengkap' => $row['nama_lengkap'],
                    'tempat_lahir' => $row['tempat_lahir'],
                    'tanggal_lahir' => $row['tanggal_lahir'],
                    'alamat' => $row['alamat'],
                    'email' => $row['email'] ?? null,
                ], [
                    'nis' => ['required', 'max:255', Rule::unique('students', 'student_id')],
                    'nama_lengkap' => ['required', 'string', 'max:255'],
                    'tempat_lahir' => ['required', 'string', 'max:255'],
                    'tanggal_lahir' => ['required', 'date'],
                    'alamat' => ['required', 'string'],
                    'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
                ], [
                    'nis.required' => 'NIS wajib diisi',
                    'nis.unique' => 'NIS sudah terdaftar',
                    'nama_lengkap.required' => 'Nama lengkap wajib diisi',
                    'tempat_lahir.required' => 'Tempat lahir wajib diisi',
                    'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
                    'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
                    'alamat.required' => 'Alamat wajib diisi',
                    'email.email' => 'Format email tidak valid',
                    'email.unique' => 'Email sudah terdaftar',
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
                    // Generate email if not provided
                    $email = $row['email'] ?? null;
                    if (!$email) {
                        $email = strtolower(str_replace(' ', '', $row['nis'])) . '@student.pondok.id';
                    }

                    // Create user
                    $newUser = User::create([
                        'name' => $row['nama_lengkap'],
                        'email' => $email,
                        'phone_number' => $row['no_hp'] ?? null,
                        'password' => Hash::make('password'),
                        'gender' => $gender,
                    ]);

                    // Assign role
                    $newUser->assignRole('student');

                    // Create student with all fields
                    Student::create([
                        'user_id' => $newUser->id,
                        'boarding_school_id' => $boardingSchoolId,
                        'student_id' => $row['nis'],
                        'gender' => $gender,
                        'status' => $status,
                        'place_of_birth' => $row['tempat_lahir'],
                        'date_of_birth' => $row['tanggal_lahir'],
                        'child_number' => $row['anak_ke'] ?? null,
                        'siblings_count' => $row['jumlah_saudara'] ?? null,
                        'address' => $row['alamat'],
                        // Father data
                        'father_name' => $row['nama_ayah'] ?? null,
                        'father_job' => $row['pekerjaan_ayah'] ?? null,
                        'father_phone' => $row['no_hp_ayah'] ?? null,
                        'father_income' => $row['penghasilan_ayah'] ?? null,
                        // Mother data
                        'mother_name' => $row['nama_ibu'] ?? null,
                        'mother_job' => $row['pekerjaan_ibu'] ?? null,
                        'mother_phone' => $row['no_hp_ibu'] ?? null,
                        'mother_income' => $row['penghasilan_ibu'] ?? null,
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
