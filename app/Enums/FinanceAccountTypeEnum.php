<?php

namespace App\Enums;

enum FinanceAccountTypeEnum: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::INCOME => 'Pemasukan',
            self::EXPENSE => 'Pengeluaran',
            self::GENERAL => 'Umum',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INCOME => 'success',
            self::EXPENSE => 'error',
            self::GENERAL => 'info',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }

    public static function labelByValue(string $value): string
    {
        return match ($value) {
            'income' => 'Pemasukan',
            'expense' => 'Pengeluaran',
            'general' => 'Umum',
            default => $value,
        };
    }
}
