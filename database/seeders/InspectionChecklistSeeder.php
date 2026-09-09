<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InspectionChecklistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fields = [
            'Fire Extinguisher' => [
                'inputs' => ['FE No', 'FE Type', 'Capacity'],
                'checks' => ['Nozzle', 'Hose', 'Pressure Indicator', 'Head Cap', 'Pin', 'Hook', 'Usage Guide', 'FE Sign']
            ],
            'Fire Hose Cabinet' => [
                'inputs' => ['Box No', 'Box'],
                'checks' => ['Hose', 'Rack', 'Nozzle', 'Valve']
            ],
            'Muster Point' => [
                'inputs' => ['ID Muster Point'],
                'checks' => ['Access', 'Visibility', 'Colour', 'Condition of Board', 'Condition of Pole', 'Letter'],
            ],
            'Fire Hydrant' => [
                'inputs' => ['Hydrant No'],
                'checks' => ['Air', 'Kaca', 'Nozzle', 'Box', 'Hose', 'Kunci Hydrant'],
            ],

            'Eyewash & Safety Shower' => [
                'inputs' => ['E&S No'],
                'checks' => ['Water', 'Caps', 'Nozzle', 'Handle', 'Access', 'Safety Light', 'Cleanliness'],
            ],
            'Fire Hose Reel' => [
                'inputs' => ['Hose Reel No'],
                'checks' => ['Hose', 'Reel', 'Nozzle', 'Valve', 'Air', 'Cover'],
            ],
            'Fire sprinkler system' => [
                'inputs' => ['Sprinkler No'],
                'checks' => ['Line Pipa', 'Main Valve', 'Drain Valve', 'Test valve', 'Alarm', 'Pressure', 'Access'],
            ],
            'Ring Buoy' => [
                'inputs' => ['Ring Buoy No'],
                'checks' => ['Ring Buoy', 'Access', 'Tempat Ring Buoy', 'Tali'],
            ],
        ];
        foreach ($fields as $type => $data) {
            // Simpan Data Default
            DB::table('inspection_checklist_masters')->insert([
                'equipment_type' => $type,
                'location_keyword' => 'Default',
                'inputs' => json_encode($data['inputs']),
                'checks' => json_encode($data['checks']),
            ]);
        }

        // KHUSUS UNTUK MAESA CAMP (Logika IF Anda sekarang pindah ke sini)
        DB::table('inspection_checklist_masters')->insert([
            'equipment_type' => 'Fire Hydrant',
            'location_keyword' => 'Maesa Camp',
            'inputs' => json_encode(['Hydrant No']),
            'checks' => json_encode(['Box', 'Hose', 'Rack', 'Valve', 'Nozel']),
        ]);
    }
}
