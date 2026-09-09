<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InspectionChecklistDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['type' => 'Fire Extinguisher', 'inputs' => ['FE No', 'FE Type', 'Capacity'], 'checks' => ['Nozzle', 'Hose', 'Pressure Indicator', 'Head Cap', 'Pin', 'Hook', 'Usage Guide', 'FE Sign']],
            ['type' => 'Fire Hose Cabinet', 'inputs' => ['Box No', 'Box'], 'checks' => ['Hose', 'Rack', 'Nozzle', 'Valve']],
            ['type' => 'Muster Point', 'inputs' => ['ID Muster Point'], 'checks' => ['Access', 'Visibility', 'Colour', 'Condition of Board', 'Condition of Pole', 'Letter']],
            ['type' => 'Fire Hydrant', 'inputs' => ['Hydrant No'], 'checks' => ['Air', 'Kaca', 'Nozzle', 'Box', 'Hose', 'Kunci Hydrant']],
            ['type' => 'Eyewash & Safety Shower', 'inputs' => ['E&S No'], 'checks' => ['Water', 'Caps', 'Nozzle', 'Handle', 'Access', 'Safety Light', 'Cleanliness']],
            ['type' => 'Fire Hose Reel', 'inputs' => ['Hose Reel No'], 'checks' => ['Hose', 'Reel', 'Nozzle', 'Valve', 'Air', 'Cover']],
            ['type' => 'Fire sprinkler system', 'inputs' => ['Sprinkler No'], 'checks' => ['Line Pipa', 'Main Valve', 'Drain Valve', 'Test valve', 'Alarm', 'Pressure', 'Access']],
            ['type' => 'Ring Buoy', 'inputs' => ['Ring Buoy No'], 'checks' => ['Ring Buoy', 'Access', 'Tempat Ring Buoy', 'Tali']],
        ];

        foreach ($data as $item) {
            DB::table('inspection_checklists')->updateOrInsert(
                ['equipment_type' => $item['type'], 'location_keyword' => 'Default'],
                ['inputs' => json_encode($item['inputs']), 'checks' => json_encode($item['checks'])]
            );
        }

        // KHUSUS LOGIKA MAESA CAMP
        DB::table('inspection_checklists')->updateOrInsert(
            ['equipment_type' => 'Fire Hydrant', 'location_keyword' => 'Maesa Camp'],
            [
                'inputs' => json_encode(['Hydrant No']),
                'checks' => json_encode(['Box', 'Hose', 'Rack', 'Valve', 'Nozel'])
            ]
        );
    }
}
