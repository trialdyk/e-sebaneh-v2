<?php

namespace App\Enums;

enum TopupStatusEnum: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran',
            self::PAID => 'Berhasil',
            self::FAILED => 'Gagal',
            self::CANCELED => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::PAID => 'green',
            self::FAILED => 'red',
            self::CANCELED => 'gray',
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
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Berhasil',
            'failed' => 'Gagal',
            'canceled' => 'Dibatalkan',
            default => $value,
        };
    }
}
