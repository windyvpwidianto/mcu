<?php

namespace App\Imports;

use App\Models\Manhour;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow; // <-- DITAMBAH: Untuk membaca baris header
use Carbon\Carbon; // <-- DITAMBAH: Untuk manipulasi tanggal

class ManhoursImport implements ToModel, WithHeadingRow // <-- DITAMBAH: Implement WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // 1. Panggil method baru untuk memformat tanggal
        $formattedDate = $this->formatDate($row['date'] ?? null); // Gunakan null coalescing untuk keamanan

        return new Manhour([
            'date'               => $formattedDate, // <-- GUNAKAN TANGGAL YANG SUDAH DIFORMAT
            'company_category'   => $row['company_category'] ?? null,
            'company'            => $row['company'] ?? null,
            'department'         => $row['department'] ?? null,
            'dept_group'         => $row['dept_group'] ?? null,
            'job_class'          => $row['job_class'] ?? null,
            'manhours'           => $row['manhours'] ?? null,
            'manpower'           => $row['manpower'] ?? null,
        ]);
    }

    /**
     * Mencoba mengurai dan memformat tanggal ke YYYY/MM/DD dari berbagai format.
     *
     * @param mixed $date
     * @return string|null
     */
    private function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }

        // 1. Penanganan Tanggal dari Excel (Format Angka)
        // Laravel Excel sering mengkonversi tanggal Excel numerik menjadi integer atau float.
        if (is_numeric($date)) {
            // Coba konversi dari timestamp/serial Excel
            try {
                // Konversi tanggal serial Excel ke object Carbon
                if (method_exists('\PhpOffice\PhpSpreadsheet\Shared\Date', 'excelToDateTimeObject')) {
                    $carbonDate = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
                    return $carbonDate->format('Y/m/d');
                }
            } catch (\Exception $e) {
                // Lanjutkan jika gagal
            }
        }

        // 2. Penanganan Tanggal dari Excel (Format String)
        $formatsToTry = [
            'Y/m/d', 'Y-m-d',
            'd-m-Y', 'd/m/Y', 'd.m.Y',    // DD-MM-YYYY
            'm-d-Y', 'm/d/Y', 'm.d.Y',    // MM-DD-YYYY
        ];

        foreach ($formatsToTry as $format) {
            try {
                $carbonDate = Carbon::createFromFormat($format, $date);
                return $carbonDate->format('Y/m/d');
            } catch (\Exception $e) {
                continue; // Lanjutkan ke format berikutnya
            }
        }

        // 3. Penanganan Default (Parse Cerdas)
        try {
            return Carbon::parse($date)->format('Y/m/d');
        } catch (\Exception $e) {
            return null; // Jika semua gagal, kembalikan null
        }
    }
}
