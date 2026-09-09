<?php

namespace App\Imports;

use App\Models\BodyPart;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BodyPartImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
   public function model(array $row)
    {
        // 1. Ambil data dasar dari Excel
        $nameId   = $row['nama_indonesia'];
        $nameEn   = $row['nama_inggris'] ?? $nameId; // Fallback ke nama ID jika EN kosong
        $category = $row['kategori'];
        $inputCode = $row['kode'] ?? null;

        // 2. Logika Gabungan untuk kolom 'code'
        // Jika kode tidak diisi di Excel, buat otomatis dari kategori + nama_en
        if (!$inputCode) {
            $prefix = Str::slug($category, '_');
            $suffix = Str::slug($nameEn, '_');
            $generatedCode = "{$prefix}_{$suffix}";
        } else {
            // Jika ada input kode, pastikan formatnya slug (kecil dan pakai underscore)
            $generatedCode = Str::slug($inputCode, '_');
        }

        return new BodyPart([
            'name'      => $nameId,
            'name_en'   => $nameEn,
            'category'  => $category,
            'code'      => $generatedCode,
            // Opsional: Jika Anda punya kolom status atau lainnya
            // 'is_active' => filter_var($row['status'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
