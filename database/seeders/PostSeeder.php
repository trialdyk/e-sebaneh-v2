<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Wisuda Santri Angkatan 2025',
                'excerpt' => 'Sebanyak 150 santri diwisuda dalam acara pelepasan santri tahun 2025 yang dihadri oleh para wali santri dan tokoh masyarakat.',
                'content' => "Pondok Pesantren Al-Hikmah menggelar wisuda santri angkatan 2025 dengan penuh khidmat. Acara ini dihadiri oleh 150 santri yang telah menyelesaikan program pendidikan selama 3-6 tahun.\n\nDalam sambutannya, pengasuh pondok menyampaikan bahwa para santri telah menunjukkan dedikasi luar biasa dalam menimba ilmu. Mereka tidak hanya unggul dalam hafalan Al-Quran, tetapi juga memiliki prestasi akademik yang membanggakan.\n\nBerbagai penghargaan diberikan kepada santri berprestasi, termasuk best hafidz, best student, dan berbagai kategori lainnya. Para orang tua tampak bahagia dan bangga menyaksikan putra-putri mereka mendapatkan pengakuan atas kerja keras selama di pondok.",
                'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800',
                'category' => 'Acara',
            ],
            [
                'title' => 'Juara 1 MTQ Tingkat Provinsi',
                'excerpt' => 'Santri kami berhasil meraih juara 1 dalam Musabaqah Tilawatil Quran (MTQ) tingkat provinsi Jawa Timur.',
                'content' => "Prestasi membanggakan kembali ditorehkan santri Pondok Pesantren Al-Hikmah. Dalam ajang MTQ tingkat provinsi yang diselenggarakan di Surabaya, santri kami berhasil membawa pulang juara 1 untuk cabang tilawah putra.\n\nIni adalah prestasi ketiga berturut-turut yang diraih dalam kompetisi bergengsi ini. Prestasi ini tidak lepas dari bimbingan intensif para ustadz dan dedikasi santri dalam berlatih.\n\nPengasuh pondok menyampaikan apresiasi setinggi-tingginya dan berharap prestasi ini dapat memotivasi santri lain untuk terus berprestasi.",
                'image' => 'https://images.unsplash.com/photo-1544776193-352d25ca82cd?w=800',
                'category' => 'Prestasi',
            ],
            [
                'title' => 'Kegiatan Bakti Sosial ke Panti Asuhan',
                'excerpt' => 'Santri bersama pengurus pondok mengadakan bakti sosial dengan membagikan sembako dan santunan ke panti asuhan terdekat.',
                'content' => "Dalam rangka menumbuhkan kepekaan sosial, Pondok Pesantren Al-Hikmah mengadakan kegiatan bakti sosial ke panti asuhan Yatim Piatu Al-Amanah.\n\nPuluhan santri dengan antusias mempersiapkan paket sembako dan dana santunan untuk dibagikan. Kegiatan ini rutin dilaksanakan setiap bulan Ramadhan sebagai bentuk kepedulian terhadap sesama.\n\nPara santri juga menghibur anak-anak panti dengan berbagai kegiatan menarik seperti games dan pembagian hadiah. Acara ini sangat berkesan bagi kedua belah pihak.",
                'image' => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=800',
                'category' => 'Kegiatan',
            ],
            [
                'title' => 'Pembukaan Tahun Ajaran Baru 2025/2026',
                'excerpt' => 'Pondok Pesantren Al-Hikmah resmi membuka tahun ajaran baru dengan menyambut 200 santri baru dari berbagai daerah.',
                'content' => "Tahun ajaran baru 2025/2026 resmi dibuka dengan meriah. Sebanyak 200 santri baru dari berbagai provinsi di Indonesia telah mendaftar dan siap memulai perjalanan menimba ilmu di Al-Hikmah.\n\nDalam masa orientasi santri baru, para santri dikenalkan dengan tata tertib pondok, sistem pembelajaran, dan berbagai kegiatan yang akan diikuti selama di pondok.\n\nPengasuh berpesan agar para santri baru dapat beradaptasi dengan cepat dan memanfaatkan waktu sebaik-baiknya untuk belajar dan berkarya.",
                'image' => 'https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?w=800',
                'category' => 'Acara',
            ],
            [
                'title' => 'Khataman Al-Quran Santri Tahfidz',
                'excerpt' => 'Lima santri berhasil menyelesaikan hafalan 30 juz Al-Quran dan mengikuti wisuda tahfidz dengan khidmat.',
                'content' => "Momen membanggakan bagi Pondok Pesantren Al-Hikmah dengan keberhasilan 5 santri menyelesaikan hafalan 30 juz Al-Quran.\n\nAcara wisuda tahfidz digelar dengan mengundang para wali santri dan tokoh masyarakat. Para santri yang lulus melanjutkan dengan program takhassus (spesialisasi) untuk menghafalkan sunnah dan matan hadits.\n\nIni membuktikan komitmen pondok dalam mencetak generasi penghafal Al-Quran yang berkualitas dan berakhlak mulia.",
                'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?w=800',
                'category' => 'Prestasi',
            ],
        ];

        foreach ($posts as $index => $post) {
            Post::create([
                ...$post,
                'slug' => Str::slug($post['title']),
                'published_at' => now()->subDays(5 - $index),
                'is_published' => true,
                'views' => rand(50, 500),
            ]);
        }
    }
}
