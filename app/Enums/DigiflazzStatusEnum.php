<?php

namespace App\Enums;

enum DigiflazzStatusEnum: string
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SUCCESS => 'Sukses',
            self::FAILED => 'Gagal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::SUCCESS => 'green',
            self::FAILED => 'red',
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
            'pending' => 'Pending',
            'success' => 'Sukses',
            'failed' => 'Gagal',
            default => $value,
        };
    }
}
