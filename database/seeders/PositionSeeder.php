<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('positions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $positions = [
            'Kepala Sekolah',
            'Wakil Kepala Sekolah',
            'Guru Kelas',
            'Guru Mata Pelajaran',
            'Wali Kelas',
            'Guru BK (Bimbingan Konseling)',
            'Guru Tahfidz',
            'Guru Bahasa Arab',
            'Guru Kitab Kuning',
            'Ustadz/Ustadzah',
            'Pengasuh Asrama',
            'Koordinator Kurikulum',
            'Koordinator Kesiswaan',
            'Bendahara',
            'Tata Usaha',
        ];

        foreach ($positions as $position) {
            Position::create(['name' => $position]);
        }
    }
}
