<?php

namespace App\Http\Controllers;

use App\Models\McuResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class McuController extends Controller
{
    public function printFitLetter($id)
    {
        $mcuResult = McuResult::with(['participant.employee', 'participant.schedule'])->findOrFail($id);

        // Hanya cetak jika statusnya fit_to_work atau fit_with_notes
        if (!in_array($mcuResult->status, ['fit_to_work', 'fit_with_notes'])) {
            return redirect()->back()->with('error', 'Surat Keterangan FIT hanya untuk karyawan yang dinyatakan FIT.');
        }

        $data = [
            'result' => $mcuResult,
            'employee' => $mcuResult->participant->employee,
            'schedule' => $mcuResult->participant->schedule,
        ];

        $pdf = Pdf::loadView('pdf.mcu_fit_letter', $data);

        // stream() agar PDF terbuka di tab browser (tidak otomatis terdownload)
        return $pdf->stream('Surat_Keterangan_FIT_' . ($mcuResult->participant->employee->name ?? 'Karyawan') . '.pdf');
    }
}
