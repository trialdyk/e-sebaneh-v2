<?php

namespace App\Enums;

enum FinanceTransactionTypeEnum: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';

    public function label(): string
    {
        return match ($this) {
            self::CREDIT => 'Kredit (Masuk)',
            self::DEBIT => 'Debit (Keluar)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CREDIT => 'green',
            self::DEBIT => 'red',
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
            'credit' => 'Kredit (Masuk)',
            'debit' => 'Debit (Keluar)',
            default => $value,
        };
    }
}
