<?php

namespace Database\Seeders;

use App\Models\ScatOption;
use Illuminate\Database\Seeder;

class ScatOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            // --- 1. UNSAFE ACTS (Tindakan Tidak Aman) ---
            ['code' => '1.2.1', 'type' => 'unsafe_act', 'name' => 'Mengoperasikan peralatan tanpa izin'],
            ['code' => '1.2.2', 'type' => 'unsafe_act', 'name' => 'Gagal / lalai memperingatkan'],
            ['code' => '1.2.3', 'type' => 'unsafe_act', 'name' => 'Gagal / lalai mengamankan'],
            ['code' => '1.2.4', 'type' => 'unsafe_act', 'name' => 'Mengoperasikan dengan kecepatan tidak sesuai'],
            ['code' => '1.2.5', 'type' => 'unsafe_act', 'name' => 'Membuat alat pengaman tidak berfungsi'],
            ['code' => '1.2.6', 'type' => 'unsafe_act', 'name' => 'Memakai alat yang rusak'],
            ['code' => '1.2.7', 'type' => 'unsafe_act', 'name' => 'Gagal / lalai menggunakan APD yang semestinya'],
            ['code' => '1.2.8', 'type' => 'unsafe_act', 'name' => 'Pembebanan yang tidak sesuai'],
            ['code' => '1.2.9', 'type' => 'unsafe_act', 'name' => 'Salah meletakkan / memuat'],
            ['code' => '1.2.10', 'type' => 'unsafe_act', 'name' => 'Pengangkatan yang tidak sesuai'],
            ['code' => '1.2.11', 'type' => 'unsafe_act', 'name' => 'Berada di tempat / posisi yang terlarang'],
            ['code' => '1.2.12', 'type' => 'unsafe_act', 'name' => 'Memperbaiki peralatan yang bekerja / bergerak'],
            ['code' => '1.2.13', 'type' => 'unsafe_act', 'name' => 'Bercanda berlebihan'],
            ['code' => '1.2.14', 'type' => 'unsafe_act', 'name' => 'Di bawah pengaruh alkohol dan/atau obat terlarang'],
            ['code' => '1.2.15', 'type' => 'unsafe_act', 'name' => 'Memakai peralatan yang bukan semestinya'],
            ['code' => '1.2.16', 'type' => 'unsafe_act', 'name' => 'Gagal / lalai mengikuti prosedur'],
            ['code' => '1.2.17', 'type' => 'unsafe_act', 'name' => 'Lainnya'],

            // --- 2. PERSONAL FACTORS (Faktor Pribadi) ---
            ['code' => '2.1.1', 'type' => 'personal_factor', 'name' => 'Tidak memadainya kemampuan fisik / fisiologis'],
            ['code' => '2.1.2', 'type' => 'personal_factor', 'name' => 'Keterbatasan mental / Kemampuan psikologi'],
            ['code' => '2.1.3', 'type' => 'personal_factor', 'name' => 'Tekanan Fisik atau fisiologis'],
            ['code' => '2.1.4', 'type' => 'personal_factor', 'name' => 'Mental atau Tekanan psikologis'],
            ['code' => '2.1.5', 'type' => 'personal_factor', 'name' => 'Kurangnya pengetahuan'],
            ['code' => '2.1.6', 'type' => 'personal_factor', 'name' => 'Kurangnya keahlian'],
            ['code' => '2.1.7', 'type' => 'personal_factor', 'name' => 'Salah Motivasi'],
            ['code' => '2.1.8', 'type' => 'personal_factor', 'name' => 'Lainnya'],

            // --- 3. JOB FACTORS (Faktor Pekerjaan) ---
            ['code' => '2.2.1', 'type' => 'job_factor', 'name' => 'Kepemimpinan dan atau Fungsi pengawasan tidak memadai'],
            ['code' => '2.2.2', 'type' => 'job_factor', 'name' => 'Engineering yang tidak memadai'],
            ['code' => '2.2.3', 'type' => 'job_factor', 'name' => 'Pembelian yang tidak memadai'],
            ['code' => '2.2.4', 'type' => 'job_factor', 'name' => 'Pemeliharaan yang tidak memadai'],
            ['code' => '2.2.5', 'type' => 'job_factor', 'name' => 'Alat dan peralatan yang tidak memadai'],
            ['code' => '2.2.6', 'type' => 'job_factor', 'name' => 'Standar-standar kerja yang tidak memadai'],
            ['code' => '2.2.7', 'type' => 'job_factor', 'name' => 'Pemakaian yang berlebihan'],
            ['code' => '2.2.8', 'type' => 'job_factor', 'name' => 'Salah pakai atau penyalahgunaan'],
            ['code' => '2.2.9', 'type' => 'job_factor', 'name' => 'Lainnya'],

            // --- 4. CONTROL SYSTEM (Sistem Kontrol) ---
            ['code' => '2.3.1', 'type' => 'control_system', 'name' => 'Perangkat Keras'],
            ['code' => '2.3.2', 'type' => 'control_system', 'name' => 'Pelatihan'],
            ['code' => '2.3.3', 'type' => 'control_system', 'name' => 'Organisasi'],
            ['code' => '2.3.4', 'type' => 'control_system', 'name' => 'Komunikasi'],
            ['code' => '2.3.5', 'type' => 'control_system', 'name' => 'Sasaran tidak kompatibel'],
            ['code' => '2.3.6', 'type' => 'control_system', 'name' => 'Prosedur'],
            ['code' => '2.3.7', 'type' => 'control_system', 'name' => 'Manajemen Pemeliharaan'],
            ['code' => '2.3.8', 'type' => 'control_system', 'name' => 'Disain'],
            ['code' => '2.3.9', 'type' => 'control_system', 'name' => 'Manajemen Resiko'],
            ['code' => '2.3.10', 'type' => 'control_system', 'name' => 'Manajemen Perubahan'],
            ['code' => '2.3.11', 'type' => 'control_system', 'name' => 'Manajemen Kontraktor'],
            ['code' => '2.3.12', 'type' => 'control_system', 'name' => 'Budaya Organisasi'],
            ['code' => '2.3.13', 'type' => 'control_system', 'name' => 'Pengaruh Peraturan'],
            ['code' => '2.3.14', 'type' => 'control_system', 'name' => 'Pembelajaran Organisasi'],
            ['code' => '2.3.15', 'type' => 'control_system', 'name' => 'Manajemen Kendaraan'],
            ['code' => '2.3.16', 'type' => 'control_system', 'name' => 'Sistem Manajemen'],
            ['code' => '2.3.17', 'type' => 'control_system', 'name' => 'Lainnya'],
        ];

        foreach ($options as $option) {
            ScatOption::updateOrCreate(
                ['code' => $option['code'], 'type' => $option['type']], // Kunci pengecekan agar tidak duplikat
                ['name' => $option['name']]
            );
        }
    }
}
