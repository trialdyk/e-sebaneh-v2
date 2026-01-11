<?php

namespace Database\Seeders;

use App\Enums\GenderEnum;
use App\Models\BoardingSchool;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BoardingSchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('admin_boarding_schools')->truncate();
        DB::table('boarding_schools')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $boardingSchools = [
            [
                'name' => 'Pondok Pesantren Al-Hikmah',
                'slug' => 'pondok-pesantren-al-hikmah',
                'address' => 'Jl. Raya Pesantren No. 123, Malang, Jawa Timur',
                'description' => 'Pondok pesantren yang fokus pada pendidikan agama dan umum dengan fasilitas lengkap.',
                'phone' => '0341-123456',
                'email' => 'info@alhikmah.sch.id',
                'admin' => [
                    'name' => 'Ahmad Fauzi',
                    'email' => 'ahmad.fauzi@alhikmah.sch.id',
                    'phone_number' => '081234567890',
                    'gender' => GenderEnum::MALE,
                ],
            ],
            [
                'name' => 'Pondok Pesantren Darul Ulum',
                'slug' => 'pondok-pesantren-darul-ulum',
                'address' => 'Jl. Pesantren Raya No. 45, Jombang, Jawa Timur',
                'description' => 'Pesantren salaf dengan tradisi kuat dalam kajian kitab kuning.',
                'phone' => '0321-654321',
                'email' => 'info@darululum.sch.id',
                'admin' => [
                    'name' => 'Siti Nurhaliza',
                    'email' => 'siti.nurhaliza@darululum.sch.id',
                    'phone_number' => '082345678901',
                    'gender' => GenderEnum::FEMALE,
                ],
            ],
            [
                'name' => 'Pondok Pesantren Nurul Huda',
                'slug' => 'pondok-pesantren-nurul-huda',
                'address' => 'Jl. Kyai Haji Abdullah No. 78, Surabaya, Jawa Timur',
                'description' => 'Pesantren modern dengan program tahfidz dan bahasa Arab intensif.',
                'phone' => '031-987654',
                'email' => 'info@nurulhuda.sch.id',
                'admin' => [
                    'name' => 'Muhammad Rizki',
                    'email' => 'muhammad.rizki@nurulhuda.sch.id',
                    'phone_number' => '083456789012',
                    'gender' => GenderEnum::MALE,
                ],
            ],
        ];

        foreach ($boardingSchools as $data) {
            // Create boarding school
            $boardingSchool = BoardingSchool::create([
                'id' => Str::uuid(),
                'name' => $data['name'],
                'slug' => $data['slug'],
                'address' => $data['address'],
                'description' => $data['description'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'balance' => 0,
            ]);

            // Create admin user for this boarding school
            $admin = User::create([
                'name' => $data['admin']['name'],
                'email' => $data['admin']['email'],
                'phone_number' => $data['admin']['phone_number'],
                'gender' => $data['admin']['gender'],
                'password' => Hash::make('password'), // Default password
                'email_verified_at' => now(),
            ]);

            // Assign admin-pondok role
            $admin->assignRole('admin-pondok');

            // Link admin to boarding school (pivot table has UUID id)
            DB::table('admin_boarding_schools')->insert([
                'id' => Str::uuid(),
                'user_id' => $admin->id,
                'boarding_school_id' => $boardingSchool->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
