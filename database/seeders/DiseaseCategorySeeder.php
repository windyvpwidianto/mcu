<?php

namespace Database\Seeders;

use App\Models\DiseaseCategory;
use Illuminate\Database\Seeder;

class DiseaseCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Cholesterol',
            'Liver Function',
            'Lungs',
            'Heart Diseases',
            'Hepatitis',
            'Hipertensi',
            'Hiperglikemi',
            'Uric Acid',
            'Electrolyte',
            'Vision',
            'Hearing'
        ];

        foreach ($categories as $name) {
            DiseaseCategory::create(['name' => $name]);
        }
    }
}
