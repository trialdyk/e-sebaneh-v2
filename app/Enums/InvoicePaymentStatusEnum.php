<?php

namespace App\Enums;

enum InvoicePaymentStatusEnum: string
{
    case UNPAID = 'unpaid';
    case PARTIAL = 'partial';
    case PAID = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Belum Lunas',
            self::PARTIAL => 'Cicilan',
            self::PAID => 'Lunas',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UNPAID => 'red',
            self::PARTIAL => 'orange',
            self::PAID => 'green',
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
            'unpaid' => 'Belum Lunas',
            'partial' => 'Cicilan',
            'paid' => 'Lunas',
            default => $value,
        };
    }
}
