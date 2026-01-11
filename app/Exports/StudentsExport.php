<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithEvents
{
    protected $filters;
    protected $isTemplate;

    public function __construct($filters = [], $isTemplate = false)
    {
        $this->filters = $filters;
        $this->isTemplate = $isTemplate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // If template, return sample data
        if ($this->isTemplate) {
            return collect([
                (object) [
                    'student_id' => '2024001',
                    'user' => (object) ['name' => 'Ahmad Fauzi', 'email' => '', 'phone_number' => '081234567890'],
                    'gender' => 'male',
                    'status' => 'active',
                    'place_of_birth' => 'Jakarta',
                    'date_of_birth' => '2010-05-15',
                    'child_number' => 2,
                    'siblings_count' => 3,
                    'address' => 'Jl. Merdeka No. 10, RT 01/RW 02',
                    'province' => 'DKI Jakarta',
                    'regency' => 'Jakarta Selatan',
                    'district' => 'Tebet',
                    'village' => 'Tebet Barat',
                    'father_name' => 'Budi Santoso',
                    'father_job' => 'Wiraswasta',
                    'father_phone' => '081122334455',
                    'father_income' => '5000000',
                    'mother_name' => 'Siti Aminah',
                    'mother_job' => 'Ibu Rumah Tangga',
                    'mother_phone' => '081122334466',
                    'mother_income' => '0',
                    'schools' => collect([]), // Sample empty schools
                ],
            ]);
        }

        $user = auth()->user();
        
        $query = Student::with(['user', 'boardingSchool', 'schools']);

        // Scope to admin's boarding schools
        if ($user->hasRole('admin-pondok')) {
            $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
            $query->whereIn('boarding_school_id', $boardingSchoolIds);
        }

        // Apply filters
        if (isset($this->filters['search']) && $this->filters['search']) {
            $query->where(function ($q) {
                $q->whereHas('user', function ($sub) {
                    $sub->where('name', 'like', "%{$this->filters['search']}%");
                })->orWhere('student_id', 'like', "%{$this->filters['search']}%");
            });
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'Nama Lengkap',
            'Email',
            'No. HP',
            'L/P',
            'Status',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Anak Ke-',
            'Jumlah Saudara',
            'Alamat',
            'Provinsi',
            'Kabupaten/Kota',
            'Kecamatan',
            'Desa/Kelurahan',
            'Nama Ayah',
            'Pekerjaan Ayah',
            'No. HP Ayah',
            'Penghasilan Ayah',
            'Nama Ibu',
            'Pekerjaan Ibu',
            'No. HP Ibu',
            'Penghasilan Ibu',
            'Sekolah Formal',
            'Tingkat Sekolah Formal',
        ];
    }

    public function map($student): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        // Convert gender to Indonesian
        $genderValue = $student->gender instanceof \BackedEnum ? $student->gender->value : $student->gender;
        $gender = match ($genderValue) {
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            default => $genderValue,
        };

        // Convert status to Indonesian
        $statusValue = $student->status instanceof \BackedEnum ? $student->status->value : $student->status;
        $status = match ($statusValue) {
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'graduated' => 'Lulus',
            'dropped_out' => 'Keluar/DO',
            default => $statusValue,
        };

        // Formal School Data
        $schoolName = '-';
        $schoolLevelName = '-';
        $school = $student->schools->first(); // Take the first attached school
        if ($school) {
            $schoolName = $school->name;
            $levelId = $school->pivot->school_level_id;
            if ($levelId) {
                // Determine level name. Optimization: Could eager load or cache, but fine for simple export
                $schoolLevelName = \App\Models\SchoolLevel::find($levelId)?->name ?? '-';
            }
        }

        return [
            $rowNumber,
            $student->student_id ?? '-',
            $student->user->name ?? '-',
            $student->user->email ?? '-',
            $student->user->phone_number ?? '-',
            $gender,
            $status,
            $student->place_of_birth ?? '-',
            $student->date_of_birth instanceof \Carbon\Carbon ? $student->date_of_birth->format('d-m-Y') : ($student->date_of_birth ?? '-'),
            $student->child_number ?? '-',
            $student->siblings_count ?? '-',
            $student->address ?? '-',
            $student->province ?? '-',
            $student->regency ?? '-',
            $student->district ?? '-',
            $student->village ?? '-',
            $student->father_name ?? '-',
            $student->father_job ?? '-',
            $student->father_phone ?? '-',
            $student->father_income ?? '-',
            $student->mother_name ?? '-',
            $student->mother_job ?? '-',
            $student->mother_phone ?? '-',
            $student->mother_income ?? '-',
            $schoolName,
            $schoolLevelName,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981'], // Green color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Get highest row and column
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Apply borders to all cells
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ]);

                // Set row height for header
                $sheet->getRowDimension(1)->setRowHeight(25);

                // Alternate row colors (zebra striping)
                for ($i = 2; $i <= $highestRow; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle('A' . $i . ':' . $highestColumn . $i)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F9FAFB'], // Light gray
                            ],
                        ]);
                    }
                }

                // Center align No column
                $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
