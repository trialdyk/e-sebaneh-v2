<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $galleries = [
            // Hero Slides
            ['title' => 'Hero 1', 'image' => 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=1200', 'category' => 'Hero', 'order' => 1],
            ['title' => 'Hero 2', 'image' => 'https://images.unsplash.com/photo-1585036156171-384164a8c675?w=1200', 'category' => 'Hero', 'order' => 2],
            ['title' => 'Hero 3', 'image' => 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?w=1200', 'category' => 'Hero', 'order' => 3],

            // Kegiatan
            ['title' => 'Kegiatan Belajar', 'image' => 'https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?w=800', 'category' => 'Kegiatan', 'order' => 1],
            ['title' => 'Kajian Kitab', 'image' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800', 'category' => 'Kegiatan', 'order' => 2],

            // Fasilitas
            ['title' => 'Masjid Pondok', 'image' => 'https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=800', 'category' => 'Fasilitas', 'order' => 1],
            ['title' => 'Asrama Santri', 'image' => 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800', 'category' => 'Fasilitas', 'order' => 2],
            ['title' => 'Perpustakaan', 'image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=800', 'category' => 'Fasilitas', 'order' => 3],
            ['title' => 'Lab Komputer', 'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800', 'category' => 'Fasilitas', 'order' => 4],

            // Prestasi
            ['title' => 'Prestasi Santri', 'image' => 'https://images.unsplash.com/photo-1544776193-352d25ca82cd?w=800', 'category' => 'Prestasi', 'order' => 1],
        ];

        foreach ($galleries as $gallery) {
            Gallery::create([
                ...$gallery,
                'is_active' => true,
            ]);
        }
    }
}
