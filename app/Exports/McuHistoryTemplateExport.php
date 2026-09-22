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
                'EMP001',           // employee_id
                'John Doe',         // full_name
                '2023-03-15',       // 2023
                '2024-04-20',       // 2024
                '2025-05-10',       // 2025
                '',                 // 2026
            ],
            [
                'EMP002',
                'Jane Smith',
                '2023-02-28',
                '2024-03-01',
                '',
                '2026-01-15',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'employee_id',
            'full_name',
            '2023',
            '2024',
            '2025',
            '2026',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
