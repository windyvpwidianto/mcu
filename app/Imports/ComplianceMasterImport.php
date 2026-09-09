<?php

namespace App\Imports;

use App\Models\ComplianceMaster;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ComplianceMasterImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Ambil data dasar
        $name = $row['name'];
        $durasi = $row['duration_months'];

        // 2. Logika Gabungan untuk kolom 'title'
        // Format: Nama (expiry in X bulan) atau Nama (Permanen)
      if ($durasi > 0) {
            $generatedTitle = "{$name} (expiry in {$durasi} bulan)";
        } else {
            $generatedTitle = "{$name} (Permanen)";
        }

        return new ComplianceMaster([
            'name'            => $name,
            'description'     => $row['description'] ?? '-',
            'class'           => $row['class'] ?? 'General',
            'duration_months' => ($durasi > 0) ? $durasi : null,
            'title'           => $generatedTitle,
            // Konversi TRUE/FALSE string dari Excel menjadi boolean
            'status'          => filter_var($row['status'], FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
