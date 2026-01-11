<?php

namespace App\Enums;

enum StudentStatusEnum: string
{
    case REGISTERED = 'registered';
    case ACTIVE = 'active';
    case ON_LEAVE = 'on_leave';
    case ALUMNI = 'alumni';
    case DROPPED_OUT = 'dropped_out';

    public function label(): string
    {
        return match ($this) {
            self::REGISTERED => 'Mendaftar',
            self::ACTIVE => 'Aktif',
            self::ON_LEAVE => 'Cuti',
            self::ALUMNI => 'Alumni',
            self::DROPPED_OUT => 'Keluar/DO',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::REGISTERED => 'gray',
            self::ACTIVE => 'green',
            self::ON_LEAVE => 'orange',
            self::ALUMNI => 'blue',
            self::DROPPED_OUT => 'red',
        };
    }

    /**
     * Get all options for dropdown
     */
    public static function options(): array
    {
        return array_map(fn ($case) => [
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
            'registered' => 'Mendaftar',
            'active' => 'Aktif',
            'on_leave' => 'Cuti',
            'alumni' => 'Alumni',
            'dropped_out' => 'Keluar/DO',
            default => $value,
        };
    }
}
