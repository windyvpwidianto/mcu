<?php

namespace App\Imports;

use Throwable;
use Carbon\Carbon;
use App\Models\Hazard;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class HazardImport implements ToModel, WithHeadingRow, SkipsOnError, WithChunkReading
{
   protected int $startId;

    public function __construct()
    {
        // Ambil ID terakhir saat import dimulai
        $last = Hazard::latest('id')->first();
        $this->startId = $last ? $last->id + 1 : 1;
    }

    public function model(array $row)
    {
        // Skip baris kosong total
        if (count(array_filter($row, fn($v) => !is_null($v) && $v !== '')) === 0) {
            return null;
        }

        // Buat nomor referensi otomatis
        $referenceNumber = 'LH-' . str_pad($this->startId++, 5, '0', STR_PAD_LEFT);

        // Parsing tanggal
        $tanggal = null;
        if (!empty($row['tanggal'])) {
            $tanggal = str_replace([' : ', '–'], [' ', '-'], $row['tanggal']);
            try {
                $tanggal = Carbon::parse($tanggal)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                $tanggal = now();
            }
        }

        return new Hazard([
            'no_referensi'                => $referenceNumber,
            'event_type_id'               => $row['event_type_id'] ?? null,
            'event_sub_type_id'           => $row['event_sub_type_id'] ?? null,
            'status'                      => $row['status'] ?? 'submitted',
            'department_id'               => $row['department_id'] ?? null,
            'contractor_id'               => $row['contractor_id'] ?? null,
            'penanggung_jawab_id'         => $row['penanggung_jawab_id'] ?? null,
            'pelapor_id'                  => $row['pelapor_id'] ?? null,
            'manualPelaporName'           => $row['manualpelaporname'] ?? null,
            'location_id'                 => $row['location_id'] ?? null,
            'location_specific'           => $row['location_specific'] ?? null,
            'tanggal'                     => $tanggal,
            'description'                 => $row['description'] ?? null,
            'doc_deskripsi'               => $row['doc_deskripsi'] ?? null,
            'immediate_corrective_action' => $row['immediate_corrective_action'] ?? null,
            'doc_corrective'              => $row['doc_corrective'] ?? null,
            'key_word'                    => $row['key_word'] ?? null,
            'kondisi_tidak_aman_id'       => $row['kondisi_tidak_aman_id'] ?? null,
            'tindakan_tidak_aman_id'      => $row['tindakan_tidak_aman_id'] ?? null,
            'consequence_id'              => $row['consequence_id'] ?? null,
            'likelihood_id'               => $row['likelihood_id'] ?? null,
            'risk_level'                  => $row['risk_level'] ?? null,
        ]);
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function onError(Throwable $error)
    {
        // Lewatkan error per baris
    }
}
