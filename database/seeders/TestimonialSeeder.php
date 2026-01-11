<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Ahmad Fauzi',
                'role' => 'Alumni 2020',
                'photo' => 'https://i.pravatar.cc/150?img=12',
                'quote' => 'Al-Hikmah membentuk saya menjadi pribadi yang lebih baik, berakhlak mulia dan memiliki ilmu yang bermanfaat.',
                'order' => 1,
            ],
            [
                'name' => 'Siti Nurhaliza',
                'role' => 'Wali Santri',
                'photo' => 'https://i.pravatar.cc/150?img=45',
                'quote' => 'Sangat puas dengan pendidikan di Al-Hikmah. Anak saya berkembang pesat baik akademik maupun spiritual.',
                'order' => 2,
            ],
            [
                'name' => 'Muhammad Rizki',
                'role' => 'Alumni 2019',
                'photo' => 'https://i.pravatar.cc/150?img=33',
                'quote' => 'Pengalaman terbaik dalam hidup saya. Guru-guru yang sangat berdedikasi dan lingkungan yang kondusif.',
                'order' => 3,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create([
                ...$testimonial,
                'is_active' => true,
            ]);
        }
    }
}
