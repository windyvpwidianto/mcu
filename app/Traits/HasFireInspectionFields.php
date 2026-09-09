<?php

namespace App\Traits;

trait HasFireInspectionFields
{
    public $fields = [
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
        'Eye Wash & Safety Shower' => [
            'inputs' => ['E&S No'],
            'checks' => ['Access', 'Signage', 'Water Flow', 'Hose Condition', 'Nozzle Condition', 'Drainage'],
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
}
