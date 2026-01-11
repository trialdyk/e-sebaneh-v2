<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CMSSeeder extends Seeder
{
    /**
     * Run the CMS database seeders.
     */
    public function run(): void
    {
        $this->call([
            SiteSettingSeeder::class,
            PostSeeder::class,
            ProgramSeeder::class,
            GallerySeeder::class,
            TestimonialSeeder::class,
            FaqSeeder::class,
        ]);
    }
}
