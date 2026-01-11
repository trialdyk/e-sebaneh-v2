<?php

namespace App\Enums;

enum InvoiceTypeEnum: string
{
    case BY_CLASSROOM = 'by_classroom';
    case BY_GENDER = 'by_gender';
    case SPECIFIC_STUDENTS = 'specific_students';
    case ALL_STUDENTS = 'all_students';

    public function label(): string
    {
        return match ($this) {
            self::BY_CLASSROOM => 'Per Kelas',
            self::BY_GENDER => 'Per Gender',
            self::SPECIFIC_STUDENTS => 'Santri Tertentu',
            self::ALL_STUDENTS => 'Semua Santri',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BY_CLASSROOM => 'blue',
            self::BY_GENDER => 'pink',
            self::SPECIFIC_STUDENTS => 'orange',
            self::ALL_STUDENTS => 'green',
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
            'by_classroom' => 'Per Kelas',
            'by_gender' => 'Per Gender',
            'specific_students' => 'Santri Tertentu',
            'all_students' => 'Semua Santri',
            default => $value,
        };
    }
}
