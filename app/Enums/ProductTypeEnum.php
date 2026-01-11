<?php

namespace App\Enums;

enum ProductTypeEnum: string
{
    case PULSA = 'pulsa';
    case DATA = 'data';
    case PLN_TOKEN = 'pln_token';
    case PLN_BILL = 'pln_bill';
    case GAME = 'game';
    case EWALLET = 'ewallet';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PULSA => 'Pulsa',
            self::DATA => 'Paket Data',
            self::PLN_TOKEN => 'Token PLN',
            self::PLN_BILL => 'Tagihan PLN',
            self::GAME => 'Voucher Game',
            self::EWALLET => 'E-Wallet',
            self::OTHER => 'Lainnya',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PULSA => 'blue',
            self::DATA => 'green',
            self::PLN_TOKEN => 'orange',
            self::PLN_BILL => 'orange',
            self::GAME => 'purple',
            self::EWALLET => 'cyan',
            self::OTHER => 'gray',
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
            'pulsa' => 'Pulsa',
            'data' => 'Paket Data',
            'pln_token' => 'Token PLN',
            'pln_bill' => 'Tagihan PLN',
            'game' => 'Voucher Game',
            'ewallet' => 'E-Wallet',
            'other' => 'Lainnya',
            default => $value,
        };
    }
}
