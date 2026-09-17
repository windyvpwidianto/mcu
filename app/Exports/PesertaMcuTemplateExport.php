<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PesertaMcuTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            [
                '12345678', // NIK
                'John Doe', // Nama Lengkap
                '1990-01-01', // Tanggal Lahir
                '081234567890', // Nomor HP
                'IT', // Departemen
                'department', // Jenis Karyawan (department/contractor)
            ],
            [
                '87654321',
                'Jane Doe',
                '1992-05-15',
                '089876543210',
                'PT. Kontraktor Maju',
                'contractor',
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Nama Lengkap',
            'Tanggal Lahir',
            'Nomor HP',
            'Departemen',
            'Jenis Karyawan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
