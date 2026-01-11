<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'title' => 'Tahfidz Al-Quran',
                'icon' => 'i-lucide-book-open',
                'description' => 'Program hafalan Al-Quran 30 juz dengan metode terbukti efektif',
                'order' => 1,
            ],
            [
                'title' => 'Kajian Kitab Kuning',
                'icon' => 'i-lucide-book',
                'description' => 'Pembelajaran kitab klasik dengan metode sorogan dan bandongan',
                'order' => 2,
            ],
            [
                'title' => 'Pendidikan Formal',
                'icon' => 'i-lucide-graduation-cap',
                'description' => 'MI, MTs, dan MA terakreditasi dengan kurikulum nasional',
                'order' => 3,
            ],
            [
                'title' => 'Bahasa Arab & Inggris',
                'icon' => 'i-lucide-languages',
                'description' => 'Program intensif bahasa asing untuk komunikasi global',
                'order' => 4,
            ],
        ];

        foreach ($programs as $program) {
            Program::create([
                ...$program,
                'slug' => Str::slug($program['title']),
                'is_active' => true,
            ]);
        }
    }
}
