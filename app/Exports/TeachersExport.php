<?php

namespace App\Exports;

use App\Models\Teacher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TeachersExport implements
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
        // If template, return dummy data
        if ($this->isTemplate) {
            return collect([
                (object) [
                    'nip' => '123456789',
                    'user' => (object) ['name' => 'Contoh Nama Pegawai', 'email' => 'pegawai@example.com', 'phone_number' => '081234567890'],
                    'position' => (object) ['name' => 'Guru Kelas'],
                    'boarding_school' => (object) ['name' => 'Nama Pondok'],
                ],
                (object) [
                    'nip' => '987654321',
                    'user' => (object) ['name' => 'Nama Pegawai Kedua', 'email' => 'pegawai2@example.com', 'phone_number' => '089876543210'],
                    'position' => (object) ['name' => 'Guru Tahfidz'],
                    'boarding_school' => (object) ['name' => 'Nama Pondok'],
                ],
            ]);
        }

        $user = auth()->user();
        
        $query = Teacher::with(['user', 'position', 'boardingSchool']);

        // Scope to admin's boarding schools
        if ($user->hasRole('admin-pondok')) {
            $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
            $query->whereIn('boarding_school_id', $boardingSchoolIds);
        }

        // Apply filters
        if (isset($this->filters['search']) && $this->filters['search']) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', "%{$this->filters['search']}%");
            });
        }

        if (isset($this->filters['position_id']) && $this->filters['position_id']) {
            $query->where('position_id', $this->filters['position_id']);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'NIP',
            'Nama Lengkap',
            'Email',
            'No. HP',
            'Jabatan',
            'Pondok',
        ];
    }

    public function map($teacher): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        return [
            $rowNumber,
            $teacher->nip ?? '-',
            $teacher->user->name ?? '-',
            $teacher->user->email ?? '-',
            $teacher->user->phone_number ?? '-',
            $teacher->position->name ?? '-',
            $teacher->boarding_school->name ?? '-',
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
                    'startColor' => ['rgb' => '4F46E5'], // Indigo color
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
