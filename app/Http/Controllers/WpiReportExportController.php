<?php

namespace App\Http\Controllers;

use App\Models\WpiReport;
use Illuminate\Http\Request;

class WpiReportExportController extends Controller
{
    /**
     * Mengambil data WPI Report untuk keperluan Export Excel.
     */
    public function getExcelData()
    {
        // Gunakan eager loading (with) agar proses cepat dan tidak membebani database
        $reports = WpiReport::with(['department_rel', 'contractor_rel', 'creator'])
            ->latest()
            ->get();

        // Transformasi data menjadi format flat (satu level)
        $data = $reports->map(function ($report) {
            // Mengolah array inspectors dari JSON menjadi String
            $inspectorsList = is_array($report->inspectors)
                ? collect($report->inspectors)->pluck('name')->filter()->implode('| ')
                : '-';

            return [
                'ID'             => $report->id,
                'No Referensi'   => $report->no_referensi,
                'Tanggal'        => $report->report_date ?? '-',
                'Waktu'          => $report->report_time,
                'Lokasi'         => $report->location,
                'Site'           => $report->site_name,
                'Area'           => $report->area,
                'reportByDept'  => $report->creator->department_name ?? '-',
                'Departemen'     => $report->department_rel?->department_name ?? "-",
                'Kontraktor'     => $report->contractor_rel?->contractor_name ?? '-',
                'Daftar Inspektur'=> $inspectorsList,
                'Status'         => strtoupper($report->status),
                'Dibuat Oleh'    => $report->creator?->name ?? 'System',
                'Reviewer'       => $report->reviewed_by,
                'Tanggal Review' => $report->review_date,
            ];
        });

        return response()->json($data);
    }
}
