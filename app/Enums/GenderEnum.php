<?php

namespace App\Enums;

enum GenderEnum: string
{
    case MALE = 'male';
    case FEMALE = 'female';

    /**
     * Get Indonesian label for gender
     */
    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Laki-laki',
            self::FEMALE => 'Perempuan',
        };
    }

    /**
     * Get all options for dropdown
     */
    public static function options(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }

    /**
     * Get label by value
     */
    public static function labelByValue(string $value): string
    {
        return match ($value) {
            'male' => 'Laki-laki',
            'female' => 'Perempuan',
            default => $value,
        };
    }
}
