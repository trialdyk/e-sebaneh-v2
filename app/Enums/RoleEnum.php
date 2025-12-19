<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SUPERADMIN = 'super-admin';
    case ADMINPONDOK = 'admin-pondok';
    case ADMINPPOB = 'admin-ppob';
    case STUDENT = 'student';
    case PARENT = 'parent';
    case TEACHER = 'teacher';

    /**
     * Get display label for role
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPERADMIN => 'Super Admin',
            self::ADMINPONDOK => 'Admin Pondok',
            self::ADMINPPOB => 'Admin PPOB',
            self::STUDENT => 'Santri',
            self::PARENT => 'Wali Santri',
            self::TEACHER => 'Guru/Ustadz',
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
     * Get all role values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
