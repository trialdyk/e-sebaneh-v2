<?php

namespace App\Enums;

enum RegistrationStatusEnum: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Verifikasi',
            self::VERIFIED => 'Terverifikasi',
            self::ACCEPTED => 'Diterima',
            self::REJECTED => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::VERIFIED => 'blue',
            self::ACCEPTED => 'green',
            self::REJECTED => 'red',
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
            'pending' => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            default => $value,
        };
    }
}
