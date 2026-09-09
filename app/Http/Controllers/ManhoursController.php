<?php

namespace App\Http\Controllers;

use App\Models\Manhour;
use Illuminate\Http\Request;

class ManhoursController extends Controller
{
   public function getExcelData()
{
    // Ambil data Manhour dengan semua relasi yang ingin ditampilkan di Excel
    $manhours = Manhour::get();

    // Mapping data agar lebih ringkas dan mudah dibaca (flattened structure)
    $data = $manhours->map(function ($manhour) {
        return [
            'ID' => $manhour->id,
            'date' => $manhour->date,
            'company_category' => $manhour->company_category,
            'company' => $manhour->company,
            'department' => $manhour->department ?? '',
            'dept_group' => $manhour->dept_group ?? '',
            'job_class' => $manhour->job_class ?? '',
            'manhours' => $manhour->manhours ?? '',
            'manpower' => $manhour->manpower ?? '',
        ];
    });

    // Kembalikan data dalam format JSON
    return response()->json($data);
}
}
