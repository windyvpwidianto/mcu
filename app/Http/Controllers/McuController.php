<?php

namespace App\Http\Controllers;

use App\Models\McuResult;
use App\Services\McuFitLetterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class McuController extends Controller
{
    /**
     * Buka antarmuka Visual Editor untuk Surat Keterangan FIT (Draft & Edit).
     */
    public function editFitLetter($id)
    {
        $mcuResult = McuResult::with([
            'record.employee',
            'record.schedule',
            'reviewedBy',
            'letterUpdatedBy'
        ])->findOrFail($id);

        // Hanya untuk status yang relevan dengan FIT atau review
        if (!in_array($mcuResult->status, ['fit_to_work', 'fit_with_notes', 'temporary_unfit', 'unfit'])) {
            return redirect()->back()->with('error', 'Surat Keterangan hanya dapat dibuat untuk hasil MCU yang sudah direview.');
        }

        // Jika nomor sertifikat belum ada, siapkan nomor baru
        if (empty($mcuResult->certificate_number)) {
            $mcuResult->certificate_number = McuFitLetterService::generateCertificateNumber($mcuResult);
            $mcuResult->save();
        }

        // Dapatkan isian surat (body) saja untuk di-edit
        $bodyContent = McuFitLetterService::extractBodyHtml($mcuResult->letter_content ?? '');
        if (empty($bodyContent)) {
            $bodyContent = McuFitLetterService::generateDefaultBodyHtml($mcuResult);
            $mcuResult->letter_content = $bodyContent;
            $mcuResult->letter_status = $mcuResult->letter_status ?? 'draft';
            $mcuResult->save();
        }

        $logoMsm = McuFitLetterService::getLogoBase64('logo-msm.png');
        $logoArchi = McuFitLetterService::getLogoBase64('logo-archi.png');

        return view('mcu.fit_letter_editor', [
            'result'      => $mcuResult,
            'employee'    => $mcuResult->record?->employee,
            'schedule'    => $mcuResult->record?->schedule,
            'bodyContent' => $bodyContent,
            'logoMsm'     => $logoMsm,
            'logoArchi'   => $logoArchi,
        ]);
    }

    /**
     * Simpan perubahan konten draf surat FIT via AJAX.
     */
    public function saveFitLetter(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
            'status'  => 'nullable|in:draft,final',
        ]);

        $mcuResult = McuResult::findOrFail($id);

        $status = $request->input('status', 'final');
        $cleanBody = McuFitLetterService::extractBodyHtml($request->input('content'));

        $mcuResult->update([
            'letter_content'    => $cleanBody,
            'letter_status'     => $status,
            'letter_updated_by' => auth()->id(),
            'letter_updated_at' => now(),
        ]);

        // Jika disimpan sebagai 'final', perbarui juga file fisik PDF di storage
        if ($status === 'final') {
            try {
                McuFitLetterService::savePdfToStorage($mcuResult);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Gagal simpan PDF fisik: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success'       => true,
            'message'       => 'Surat berhasil disimpan sebagai ' . (strtoupper($status)),
            'letter_status' => $mcuResult->letter_status,
            'updated_at'    => $mcuResult->letter_updated_at ? \Carbon\Carbon::parse($mcuResult->letter_updated_at)->format('d M Y H:i') : null,
            'updated_by'    => auth()->user()?->name ?? 'User',
        ]);
    }

    /**
     * Reset isi surat kembali ke format template default.
     */
    public function resetFitLetter($id)
    {
        $mcuResult = McuResult::with(['record.employee', 'record.schedule', 'reviewedBy'])->findOrFail($id);

        $defaultBody = McuFitLetterService::generateDefaultBodyHtml($mcuResult);

        $mcuResult->update([
            'letter_content'    => $defaultBody,
            'letter_status'     => 'draft',
            'letter_updated_by' => auth()->id(),
            'letter_updated_at' => now(),
        ]);

        return response()->json([
            'success'       => true,
            'message'       => 'Isian surat berhasil di-reset ke format standar.',
            'content'       => $defaultBody,
            'letter_status' => 'draft',
        ]);
    }

    /**
     * Cetak / Stream PDF surat keterangan FIT.
     */
    public function printFitLetter($id)
    {
        $mcuResult = McuResult::with(['record.employee', 'record.schedule', 'reviewedBy'])->findOrFail($id);

        // Hanya cetak jika statusnya fit_to_work atau fit_with_notes
        if (!in_array($mcuResult->status, ['fit_to_work', 'fit_with_notes'])) {
            return redirect()->back()->with('error', 'Surat Keterangan FIT hanya untuk karyawan yang dinyatakan FIT.');
        }

        // Render PDF menggunakan service yang memprioritaskan letter_content tersimpan
        $pdf = McuFitLetterService::renderPdf($mcuResult);

        $employeeName = $mcuResult->record?->employee?->name ?? 'Karyawan';
        return $pdf->stream('Surat_Keterangan_FIT_' . str_replace(' ', '_', $employeeName) . '.pdf');
    }
}
