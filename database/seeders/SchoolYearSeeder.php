<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

class SchoolYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolYears = [
            ['name' => '2022/2023', 'is_active' => false],
            ['name' => '2023/2024', 'is_active' => false],
            ['name' => '2024/2025', 'is_active' => true],
            ['name' => '2025/2026', 'is_active' => false],
        ];

        foreach ($schoolYears as $schoolYear) {
            SchoolYear::create($schoolYear);
        }
    }
}
