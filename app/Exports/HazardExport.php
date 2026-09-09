<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HazardExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        // Menggunakan query yang sudah difilter dari Livewire
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'No. Referensi',
            'Event Type',
            'Event Sub Type',
            'Department/Contractor',
            'Pelapor',
            'Dept Pelapor',
            'Status',
            'Deskripsi',
            'Tanggal',
            'Due Dates / Closed',
        ];
    }

    public function map($report): array
    {
        return [
            $report->no_referensi,
            $report->eventType->event_type_name ?? '-',
            $report->eventSubType->event_sub_type_name ?? '-',
            $report->department->department_name ?? $report->contractor->contractor_name ?? '-',
            $report->pelapor->name ?? $report->manualPelaporName,
            $report->pelapor->department_name ?? 'N/A',
            str_replace('_', ' ', $report->status),
            strip_tags($report->description),
            \Carbon\Carbon::parse($report->tanggal)->format('d M Y'),
            $report->total_due_dates . ' / ' . $report->pending_actual_closes,
        ];
    }
}
