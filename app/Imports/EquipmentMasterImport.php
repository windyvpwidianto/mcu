<?php

namespace App\Imports;

use App\Models\EquipmentMaster;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets; // Tambahkan ini

class EquipmentMasterImport implements ToModel, WithHeadingRow, WithMapping, WithMultipleSheets
{
    protected $type, $location_id;

    public function __construct($type, $location_id)
    {
        $this->type = $type;
        $this->location_id = $location_id;
    }

    /**
     * Menentukan sheet mana yang akan diproses berdasarkan nama sheet
     */
    public function sheets(): array
    {
        $available_types = [
            'Fire Extinguisher',
            'Fire Hydrant',
            'Fire Hose Reel',
            'Fire sprinkler system',
            'Ring Buoy',
            'Eyewash & Safety Shower',
            'Muster Point',
            'Alarm',
            'First Aid Box',
            'Rescue Car',
            'Fire & Rescue Equipment'
        ];

        // 1. Decode entitas HTML (mengubah &amp; kembali menjadi &)
        // 2. Trim untuk menghapus spasi liar yang mungkin ada di variabel
        $normalizedType = trim(htmlspecialchars_decode($this->type));

        // Mencari posisi index berdasarkan nama yang sudah dinormalisasi
        $sheetIndex = array_search($normalizedType, $available_types);

        // Jika ditemukan, gunakan index angka tersebut sebagai kunci
        if ($sheetIndex !== false) {
            return [
                $sheetIndex => $this,
            ];
        }

        // Default jika tidak ketemu (ambil sheet pertama index 0)
        // Atau bisa juga lempar Exception jika Anda ingin memastikan user memilih sheet yang benar
        return [
            0 => $this,
        ];
    }

    /**
     * Memastikan data dipetakan dengan benar terlepas dari spasi/case di header excel
     */
    public function map($row): array
    {
        $mapped = [];
        foreach ($row as $key => $value) {
            // Bersihkan key: lowercase dan ganti spasi dengan underscore
            $cleanKey = str_replace([' ', '-'], '_', strtolower(trim($key)));
            $mapped[$cleanKey] = $value;
        }
        return $mapped;
    }

    public function model(array $row)
    {
        // Validasi: Jika baris kosong (misal hanya baris header tanpa isi), jangan simpan
        if (empty(array_filter($row))) {
            return null;
        }

        // Ambil 'Lokasi Spesifik'
        $specificLocation = $row['lokasi'] ?? $row['name'] ?? $row['lokasi_spesifik'] ?? $row['location'] ?? null;

        // Data teknis JSON
        $technicalFields = [
            'FE No'           => $row['fe_no'] ?? null,
            'FE Type'         => $row['fe_type'] ?? null,
            'Capacity'        => $row['capacity'] ?? null,
            'Box No'          => $row['box_nomor'] ?? $row['box_no'] ?? null,
            'ID No'           => $row['id_nomor'] ?? $row['id_no'] ?? null,
            'ID Muster Point' => $row['id_muster_point'] ?? null,
            'Hydrant No'      => $row['hydrant_no'] ?? null,
            'E&S No'          => $row['nomor'] ?? $row['es_no'] ?? null,
            'Hose Reel No'    => $row['hose_reel_no'] ?? null,
            'Sprinkler No'    => $row['sprinkler_no'] ?? null,
            'Ring Buoy No'    => $row['ring_buoy_no'] ?? null,
            'Alarm No'        => $row['alarm_no'] ?? null,
            'Quantity'        => $row['quantity'] ?? null,
        ];

        // Bersihkan field null
        $technicalData = array_filter($technicalFields, fn($value) => !is_null($value));

        return new EquipmentMaster([
            'type'              => $this->type,
            'location_id'       => $this->location_id,
            'specific_location' => $specificLocation,
            'technical_data'    => $technicalData, // Pastikan model meng-cast ini ke array/json
            'is_active'         => true,
        ]);
    }
}
