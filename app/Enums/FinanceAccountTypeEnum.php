<?php

namespace App\Enums;

enum FinanceAccountTypeEnum: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::INCOME => 'Pemasukan',
            self::EXPENSE => 'Pengeluaran',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INCOME => 'green',
            self::EXPENSE => 'red',
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
            default => $value,
        };
    }
}
