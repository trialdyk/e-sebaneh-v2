<?php

namespace App\Exports;

use App\Models\StudentWithdrawHistory;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;use Carbon\Carbon;

class StudentWithdrawHistoryExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $user = auth()->user();
        $boardingSchoolId = $user->boardingSchools()->first()?->id;

        $query = StudentWithdrawHistory::query()
            ->with('student.user')
            ->whereHas('student', fn($q) => $q->where('boarding_school_id', $boardingSchoolId));

        // Apply filters
        if (!empty($this->filters['student_id']) && $this->filters['student_id'] !== 'all') {
            $query->where('student_id', $this->filters['student_id']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('date', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('date', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['type']) && $this->filters['type'] !== 'all') {
            $query->where('type', $this->filters['type']);
        }

        return $query->latest('date')->latest('id');
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'NIS',
            'Nama Santri',
            'Tipe Transaksi',
            'Jumlah (Rp)',
            'Keterangan',
        ];
    }

    public function map($history): array
    {
        $typeLabels = [
            'topup' => 'Topup via Mobile App',
            'topup_by_admin' => 'Topup oleh Admin',
            'withdraw' => 'Penarikan',
        ];

        return [
            Carbon::parse($history->date)->format('d/m/Y H:i'),
            $history->student->student_id ?? '-',
            $history->student->user->name ?? '-',
            $typeLabels[$history->type] ?? $history->type,
            number_format($history->amount, 0, ',', '.'),
            $history->description ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Riwayat Transaksi';
    }
}
