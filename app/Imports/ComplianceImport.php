<?php

namespace App\Imports;

use App\Models\Compliance;
use App\Models\ComplianceMaster;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ComplianceImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Cari User berdasarkan nama (header: nama_user)
        // trim() digunakan untuk menghapus spasi di awal/akhir teks
        $user = User::where('name', trim($row['nama_user']))->first();
        // 2. Cari Master berdasarkan nama (header: compliance_name)
        $master = ComplianceMaster::where('name', trim($row['compliance_name']))->first();
        // Jika salah satu tidak ditemukan, baris ini dilewati
        if (!$user || !$master) {
            return null;
        }
        // 3. Olah Tanggal Mulai (header: start_date)
        try {
            // Cek apakah tanggal dari excel berupa angka (format excel) atau string
            if (is_numeric($row['start_date'])) {
                $startDate = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['start_date']));
            } else {
                $startDate = Carbon::parse($row['start_date']);
            }
        } catch (\Exception $e) {
            return null; // Skip jika format tanggal rusak
        }

        // 4. Hitung Expired At berdasarkan durasi di Master
        $expiredAt = null;
        if ($master->duration_months > 0) {
            $expiredAt = $startDate->copy()->addMonths($master->duration_months);
        }

        return new Compliance([
            'user_id'              => $user->id,
            'compliance_master_id' => $master->id,
            'start_date'           => $startDate->format('Y-m-d'),
            'expired_at'           => $expiredAt ? $expiredAt->format('Y-m-d') : null,
            'status'               => true,
        ]);
    }
}
