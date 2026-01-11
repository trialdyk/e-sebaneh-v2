<?php

namespace App\Exports;

use App\Models\SavingsTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class SavingsTransactionsExport implements FromQuery, WithHeadings, WithMapping, WithTitle
{
    protected $studentId;
    protected $filters;

    public function __construct($studentId, array $filters = [])
    {
        $this->studentId = $studentId;
        $this->filters = $filters;
    }

    public function query()
    {
        $query = SavingsTransaction::query()
            ->where('student_id', $this->studentId);

        // Apply filters
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['type']) && $this->filters['type'] !== 'all') {
            $query->where('type', $this->filters['type']);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Tipe Transaksi',
            'Jumlah (Rp)',
            'Saldo Setelah (Rp)',
            'Keterangan',
        ];
    }

    public function map($transaction): array
    {
        $typeLabels = [
            'deposit' => 'Setor',
            'withdrawal' => 'Tarik',
        ];

        return [
            Carbon::parse($transaction->created_at)->format('d/m/Y H:i'),
            $typeLabels[$transaction->type] ?? $transaction->type,
            number_format($transaction->amount, 0, ',', '.'),
            number_format($transaction->balance_after, 0, ',', '.'),
            $transaction->notes ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Riwayat Tabungan';
    }
}
