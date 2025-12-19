<?php

namespace Database\Seeders;

use App\Enums\GenderEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test accounts for each role
        $testUsers = [
            [
                'name' => 'Super Admin',
                'email' => 'super-admin@example.com',
                'role' => RoleEnum::SUPERADMIN,
                'gender' => GenderEnum::MALE,
            ],
            [
                'name' => 'Admin Pondok',
                'email' => 'admin-pondok@example.com',
                'role' => RoleEnum::ADMINPONDOK,
                'gender' => GenderEnum::MALE,
            ],
            [
                'name' => 'Admin PPOB',
                'email' => 'admin-ppob@example.com',
                'role' => RoleEnum::ADMINPPOB,
                'gender' => GenderEnum::FEMALE,
            ],
            [
                'name' => 'Ahmad Santri',
                'email' => 'student@example.com',
                'role' => RoleEnum::STUDENT,
                'gender' => GenderEnum::MALE,
            ],
            [
                'name' => 'Budi Wali',
                'email' => 'parent@example.com',
                'role' => RoleEnum::PARENT,
                'gender' => GenderEnum::MALE,
            ],
            [
                'name' => 'Ustadz Hasan',
                'email' => 'teacher@example.com',
                'role' => RoleEnum::TEACHER,
                'gender' => GenderEnum::MALE,
            ],
        ];

        foreach ($testUsers as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                    'balance' => rand(100000, 1000000),
                ])
            );

            $user->assignRole($role->value);
        }
    }
}
