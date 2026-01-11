<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site_name', 'value' => 'Pondok Pesantren Al-Hikmah', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'Membentuk Generasi Berakhlak Mulia, Berilmu Tinggi', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_subtitle', 'value' => 'Sejak 1999', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_description', 'value' => 'Pondok Pesantren Al-Hikmah adalah lembaga pendidikan Islam yang berfokus pada pembentukan karakter Islami dan pengembangan ilmu pengetahuan.', 'type' => 'textarea', 'group' => 'general'],
            ['key' => 'logo', 'value' => null, 'type' => 'image', 'group' => 'general'],
            ['key' => 'visi', 'value' => 'Menjadi lembaga pendidikan Islam terdepan yang melahirkan generasi berakhlakul karimah, berilmu tinggi, dan bermanfaat bagi umat.', 'type' => 'textarea', 'group' => 'general'],
            ['key' => 'misi', 'value' => json_encode([
                'Menyelenggarakan pendidikan Islam yang berkualitas dan berkarakter',
                'Membina santri menjadi hafidz dan hafidzah Al-Quran',
                'Mengintegrasikan ilmu agama dan ilmu umum secara seimbang',
                'Membangun lingkungan islami yang kondusif untuk belajar',
                'Mencetak kader-kader ulama dan pemimpin umat',
            ]), 'type' => 'json', 'group' => 'general'],

            // Stats
            ['key' => 'stats_santri', 'value' => '500+', 'type' => 'text', 'group' => 'general'],
            ['key' => 'stats_guru', 'value' => '45', 'type' => 'text', 'group' => 'general'],
            ['key' => 'stats_tahun', 'value' => '1999', 'type' => 'text', 'group' => 'general'],
            ['key' => 'stats_alumni', 'value' => '2000+', 'type' => 'text', 'group' => 'general'],

            // Contact
            ['key' => 'contact_address', 'value' => 'Jl. Raya Pesantren No. 123, Malang, Jawa Timur 65151', 'type' => 'textarea', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '0341-123456', 'type' => 'text', 'group' => 'contact'],
            ['key' => 'contact_whatsapp', 'value' => '081234567890', 'type' => 'text', 'group' => 'contact'],
            ['key' => 'contact_email', 'value' => 'info@alhikmah.sch.id', 'type' => 'text', 'group' => 'contact'],

            // Social
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/alhikmah', 'type' => 'text', 'group' => 'social'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/alhikmah', 'type' => 'text', 'group' => 'social'],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com/@alhikmah', 'type' => 'text', 'group' => 'social'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
