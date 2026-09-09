<?php

namespace App\Http\Controllers;

use App\Models\Hazard;
use Illuminate\Http\Request;

class HazardController extends Controller
{
    public function getExcelData()
{
    // Ambil data Hazard dengan semua relasi yang ingin ditampilkan di Excel
    $hazards = Hazard::with([
        'eventType',
        'eventSubType',
        'department',
        'contractor',
        'penanggungJawab',
        'pelapor',
        'location',
        'consequence',
        'likelihood',
        'hazardKondisiTidakAman',
        'hazardTindakanTidakAman',
    ])->get();

    // Mapping data agar lebih ringkas dan mudah dibaca (flattened structure)
    $data = $hazards->map(function ($hazard) {
        return [
            'ID' => $hazard->id,
            'No_Referensi' => $hazard->no_referensi,
            'Tanggal_Kejadian' => $hazard->tanggal,
            'Status' => $hazard->status,
            'Tipe_Event' => $hazard->eventType->event_type_name ?? '',
            'Sub_Tipe_Event' => $hazard->eventSubType->event_sub_type_name ?? '',
            'Departemen' => $hazard->department->department_name ?? '',
            'Kontraktor' => $hazard->contractor->contractor_name ?? '',
            'Pelapor_Nama' => $hazard->pelapor->name ?? $hazard->manualPelaporName,
            'dept/contractor_pelapor' => $hazard->pelapor->department_name ?? '',
            'Deskripsi_Hazard' => $hazard->description,
            'Tingkat_Risiko' => $hazard->risk_level ?? '',
            'Penanggung_Jawab' => $hazard->penanggungJawab->name ?? '',
            'Lokasi' => $hazard->location->name ?? '',
            'Konsekuensi' => $hazard->consequence->consequence_name ?? '',
            'Kemungkinan' => $hazard->likelihood->likelihood_name ?? '',
            'lokasi_spesifik' => $hazard->location_specific,
            'tindakan_korektif_segera' => $hazard->immediate_corrective_action,
            'kata_kunci' => $hazard->key_word,
            'kondisi_tidak_aman_id' => $hazard->hazardKondisiTidakAman->name ?? '',
            'tindakan_tidak_aman_id' => $hazard->hazardTindakanTidakAman->name ?? '',
            // ... tambahkan semua kolom yang Anda butuhkan
        ];
    });

    // Kembalikan data dalam format JSON
    return response()->json($data);
}
}
