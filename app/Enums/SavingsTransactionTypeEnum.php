<?php

namespace App\Enums;

enum SavingsTransactionTypeEnum: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'Setor Tunai',
            self::WITHDRAWAL => 'Tarik Tunai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DEPOSIT => 'green',
            self::WITHDRAWAL => 'red',
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
            'deposit' => 'Setor Tunai',
            'withdrawal' => 'Tarik Tunai',
            default => $value,
        };
    }
}
