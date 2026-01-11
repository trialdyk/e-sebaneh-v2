<?php

namespace App\Enums;

enum InvoiceForGenderEnum: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case ALL = 'all';

    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Putra',
            self::FEMALE => 'Putri',
            self::ALL => 'Semua (Putra & Putri)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MALE => 'blue',
            self::FEMALE => 'pink',
            self::ALL => 'gray',
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
            'male' => 'Putra',
            'female' => 'Putri',
            'all' => 'Semua (Putra & Putri)',
            default => $value,
        };
    }
}
