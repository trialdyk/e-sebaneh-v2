<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case TOPUP = 'topup';
    case TOPUP_BY_ADMIN = 'topup_by_admin';
    case WITHDRAW = 'withdraw';
    case TRANSFER = 'transfer';
    case PPOB = 'ppob';
    case STUDENT_TOPUP = 'student_topup';

    public function label(): string
    {
        return match ($this) {
            self::TOPUP => 'Topup via Mobile App',
            self::TOPUP_BY_ADMIN => 'Topup oleh Admin',
            self::WITHDRAW => 'Tarik Saldo',
            self::TRANSFER => 'Transfer',
            self::PPOB => 'Pembelian PPOB',
            self::STUDENT_TOPUP => 'Topup Santri',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TOPUP => 'green',
            self::TOPUP_BY_ADMIN => 'blue',
            self::WITHDRAW => 'red',
            self::TRANSFER => 'blue',
            self::PPOB => 'purple',
            self::STUDENT_TOPUP => 'orange',
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
            'topup' => 'Topup via Mobile App',
            'topup_by_admin' => 'Topup oleh Admin',
            'withdraw' => 'Tarik Saldo',
            'transfer' => 'Transfer',
            'ppob' => 'Pembelian PPOB',
            'student_topup' => 'Topup Santri',
            default => $value,
        };
    }
}
