<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('import:states');
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SchoolYearSeeder::class,
            SchoolSeeder::class,
            PositionSeeder::class,
            BoardingSchoolSeeder::class,
            SurahSeeder::class,
            CMSSeeder::class,
            SystemFinanceAccountSeeder::class,
        ]);
    }
}
