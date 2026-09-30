<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class McuHistoryTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            [
                '3201234567890001', // NIK
                'EMP001',           // Employee ID
                'John Doe',         // Nama Lengkap
                'L',                // Jenis Kelamin
                '1990-01-15',       // Tanggal Lahir
                '081234567890',     // Nomor Hp
                
                // Riwayat 2023
                '2023-05-20',       // 2023_Tanggal
                'Hadir',            // 2023_Kehadiran
                'Selesai',          // 2023_Status
                'Fit to Work',      // 2023_Medis
                
                // Riwayat 2024
                '2024-05-22',       // 2024_Tanggal
                'Hadir',            // 2024_Kehadiran
                'Selesai',          // 2024_Status
                'Fit with Notes',   // 2024_Medis

                // Riwayat 2025
                '', '', '', ''      // Kosong untuk 2025
            ],
            [
                '3201234567890002', 
                'EMP002',           
                'Jane Smith',       
                'P',                
                '1992-08-30',       
                '081298765432',     
                
                // Riwayat 2023 (Kosong)
                '', '', '', '',

                // Riwayat 2024 (Kosong)
                '', '', '', '',

                // Riwayat 2025
                '2025-06-10',       
                'Tidak Hadir',      
                'Batal',            
                '',                 
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Employee ID',
            'Nama Lengkap',
            'Jenis Kelamin (L/P)',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Nomor Hp',

            '2023_Tanggal',
            '2023_Kehadiran',
            '2023_Status',
            '2023_Medis',

            '2024_Tanggal',
            '2024_Kehadiran',
            '2024_Status',
            '2024_Medis',

            '2025_Tanggal',
            '2025_Kehadiran',
            '2025_Status',
            '2025_Medis',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
