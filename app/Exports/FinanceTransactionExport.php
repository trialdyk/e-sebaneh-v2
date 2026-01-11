<?php

namespace App\Exports;

use App\Models\FinanceTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FinanceTransactionExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $boardingSchoolId;
    protected $startDate;
    protected $endDate;
    protected $accountId;

    public function __construct($boardingSchoolId, $startDate, $endDate, $accountId)
    {
        $this->boardingSchoolId = $boardingSchoolId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->accountId = $accountId;
    }

    public function query()
    {
        $query = FinanceTransaction::query()
            ->with(['account', 'user'])
            ->whereHas('account', function($q) {
                $q->where('boarding_school_id', $this->boardingSchoolId);
            });

        if ($this->startDate) {
            $query->whereDate('date', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('date', '<=', $this->endDate);
        }
        if ($this->accountId) {
            $query->where('finance_account_id', $this->accountId);
        }

        return $query->latest('date')->latest('created_at');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tanggal',
            'Pos Keuangan',
            'Jenis Pos',
            'Nominal',
            'Tipe Transaksi',
            'Deskripsi',
            'Admin',
            'Referensi ID',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->date->format('d/m/Y'),
            $transaction->account->name,
            $transaction->account->type->label(),
            $transaction->amount,
            $transaction->type->value === 'credit' ? 'Pemasukan' : 'Pengeluaran',
            $transaction->description,
            $transaction->user?->name ?? 'System',
            $transaction->reference_id ?? '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
