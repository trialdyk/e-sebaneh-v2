<?php

namespace App\Enums;

enum WithdrawalStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Konfirmasi',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::COMPLETED => 'Selesai Transfer',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::APPROVED => 'blue',
            self::REJECTED => 'red',
            self::COMPLETED => 'green',
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
            'pending' => 'Menunggu Konfirmasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai Transfer',
            default => $value,
        };
    }
}
