<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('schools')->truncate();
        DB::table('school_levels')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $schools = [
            'SD' => ['name' => 'Sekolah Dasar', 'levels' => ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6']],
            'MI' => ['name' => 'Madrasah Ibtidaiyah', 'levels' => ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6']],
            'SMP' => ['name' => 'Sekolah Menengah Pertama', 'levels' => ['Kelas 7', 'Kelas 8', 'Kelas 9']],
            'MTs' => ['name' => 'Madrasah Tsanawiyah', 'levels' => ['Kelas 7', 'Kelas 8', 'Kelas 9']],
            'SMA' => ['name' => 'Sekolah Menengah Atas', 'levels' => ['Kelas 10', 'Kelas 11', 'Kelas 12']],
            'SMK' => ['name' => 'Sekolah Menengah Kejuruan', 'levels' => ['Kelas 10', 'Kelas 11', 'Kelas 12']],
            'MA' => ['name' => 'Madrasah Aliyah', 'levels' => ['Kelas 10', 'Kelas 11', 'Kelas 12']],
        ];

        foreach ($schools as $shortName => $data) {
            $school = School::create([
                'name' => $data['name'],
                'short_name' => $shortName,
            ]);

            foreach ($data['levels'] as $index => $levelName) {
                $school->schoolLevels()->create([
                    'name' => $levelName,
                    'order_level' => $index + 1,
                ]);
            }
        }
    }
}
