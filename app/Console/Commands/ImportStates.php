<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Console\Command;

class ImportStates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:states';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import district and village to the table';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $data_provinces = file_get_contents(storage_path() . "/imports/propinsi.json");

        $provinces = json_decode($data_provinces);
        foreach ($provinces as $province) {
            Province::updateOrCreate(
                ['id' => $province->id],
                [
                    'id' => $province->id,
                    'name' => $province->nama
                ]
            );
            $data_regencies = file_get_contents(storage_path() . "/imports/kabupaten/".$province->id.'.json');
            $regencies = json_decode($data_regencies);
            foreach($regencies as $regency){
                Regency::updateOrCreate(
                    ['id' => $regency->id],
                    [
                        'province_id' => $province->id,
                        'id' => $regency->id,
                        'name' => $regency->nama
                    ]
                );
                $data_districts = file_get_contents(storage_path() . '/imports/kecamatan/'.$regency->id.'.json');
                $districts = json_decode($data_districts);
                foreach($districts as $district){
                    District::updateOrCreate(
                        ['id' => $district->id],
                        [
                            'regency_id' => $regency->id,
                            'id' => $district->id,
                            'name' => $district->nama
                        ]
                    );
                    $data_villages = file_get_contents(storage_path() . '/imports/kelurahan/'.$district->id.'.json');
                    $villages = json_decode($data_villages);
                    foreach($villages as $village){
                        Village::updateOrCreate(
                            ['id' => $village->id],
                            [
                                'district_id' => $district->id,
                                'id' => $village->id,
                                'name' => $village->nama
                            ]
                        );
                    }
                }
            }
            echo "Berhasil Mengimport Data Provinsi " . $province->nama . "\n";
        }
    }
}
