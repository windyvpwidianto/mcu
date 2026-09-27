<?php

namespace App\Services;

use App\Models\McuResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class McuFitLetterService
{
    /**
     * Dapatkan logo dalam format base64 agar kompatibel 100% di browser editor maupun DomPDF.
     */
    public static function getLogoBase64(string $filename): string
    {
        $path = public_path('images/' . $filename);
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return '';
    }

    /**
     * Generate nomor sertifikat baru jika belum ada.
     */
    public static function generateCertificateNumber(McuResult $result): string
    {
        if (!empty($result->certificate_number)) {
            return $result->certificate_number;
        }

        $currentYear = date('Y');
        $currentMonth = date('m');
        $romans = [
            '01' => 'I', '02' => 'II', '03' => 'III', '04' => 'IV',
            '05' => 'V', '06' => 'VI', '07' => 'VII', '08' => 'VIII',
            '09' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'
        ];
        $romanMonth = $romans[$currentMonth] ?? 'I';

        $latestCert = McuResult::whereYear('reviewed_at', $currentYear)
            ->whereMonth('reviewed_at', $currentMonth)
            ->whereNotNull('certificate_number')
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latestCert && preg_match('/^(\d{3})\//', $latestCert->certificate_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }

        $certNumberStr = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        return "{$certNumberStr}/KT/MCU/{$romanMonth}/{$currentYear}";
    }

    /**
     * Ekstrak konten isian surat (<main>) dari HTML lengkap atau kembalikan as-is jika sudah berupa isian.
     */
    public static function extractBodyHtml(?string $html): string
    {
        if (empty($html)) return '';

        // Jika terdapat tag <main>...</main>
        if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $html, $matches)) {
            return trim($matches[1]);
        }

        // Jika terdapat antara </header> dan <footer
        if (preg_match('/<\/header>(.*?)<footer/s', $html, $matches)) {
            return trim($matches[1]);
        }

        // Jika terdapat <html> tetapi tidak ada <main>
        if (strpos($html, '<html') !== false) {
            $clean = preg_replace('/<header.*?<\/header>/s', '', $html);
            $clean = preg_replace('/<footer.*?<\/footer>/s', '', $clean);
            $clean = preg_replace('/^.*?<body[^>]*>/s', '', $clean);
            $clean = preg_replace('/<\/body>.*?$/s', '', $clean);
            return trim($clean);
        }

        return trim($html);
    }

    /**
     * Bangkitkan HTML isian default (<main>) untuk editor.
     */
    public static function generateDefaultBodyHtml(McuResult $result): string
    {
        $full = self::generateDefaultHtml($result);
        return self::extractBodyHtml($full);
    }

    /**
     * Bangkitkan HTML template default untuk surat keterangan FIT.
     */
    public static function generateDefaultHtml(McuResult $result, ?string $bodyContent = null): string
    {
        $result->loadMissing(['record.employee', 'record.schedule', 'reviewedBy']);

        $employee = $result->record?->employee;
        $schedule = $result->record?->schedule;
        $certNumber = $result->certificate_number ?? self::generateCertificateNumber($result);

        $logoMsm = self::getLogoBase64('logo-msm.png');
        $logoArchi = self::getLogoBase64('logo-archi.png');

        return view('pdf.mcu_fit_letter_template', [
            'result'         => $result,
            'employee'       => $employee,
            'schedule'       => $schedule,
            'fullCertNumber' => $certNumber,
            'logoMsm'        => $logoMsm,
            'logoArchi'      => $logoArchi,
            'bodyContent'    => $bodyContent,
        ])->render();
    }

    /**
     * Render PDF resmi dengan Header & Footer paten dari template master dan isian surat yang diedit.
     */
    public static function renderPdf(McuResult $result)
    {
        $body = !empty($result->letter_content) 
            ? self::extractBodyHtml($result->letter_content) 
            : null;

        $html = self::generateDefaultHtml($result, $body);

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'Times New Roman'
        ]);

        return $pdf;
    }

    /**
     * Simpan file PDF ke storage dan perbarui certificate_path.
     */
    public static function savePdfToStorage(McuResult $result): string
    {
        $employeeName = $result->record?->employee?->name ?? 'Karyawan';
        $fileName = 'Fit_to_Work_' . str_replace(' ', '_', $employeeName) . '_' . time() . '.pdf';
        $savePathDir = storage_path('app/public/mcu_certificates/');

        if (!file_exists($savePathDir)) {
            mkdir($savePathDir, 0755, true);
        }

        $pdf = self::renderPdf($result);
        $fullPath = $savePathDir . $fileName;
        $pdf->save($fullPath);

        $relativePath = 'mcu_certificates/' . $fileName;
        $result->update([
            'certificate_path' => $relativePath,
        ]);

        return $relativePath;
    }
}
