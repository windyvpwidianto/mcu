<?php

namespace App\Services;

use App\Models\McuResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class McuReferralLetterService
{
    /**
     * Dapatkan logo dalam format base64 agar kompatibel 100% di browser editor maupun DomPDF.
     */
    public static function getLogoBase64(string $filename): string
    {
        return McuFitLetterService::getLogoBase64($filename);
    }

    /**
     * Bangkitkan HTML template formulir rujukan medis (TT-OHS-FRO-028D).
     */
    public static function generateHtml(McuResult $result): string
    {
        $result->loadMissing(['record.employee', 'diseaseCategories', 'reviewedBy']);

        $employee = $result->record?->employee;
        $logoMsm = self::getLogoBase64('logo-msm.png');
        $logoArchi = self::getLogoBase64('logo-archi.png');

        // Umur karyawan
        $age = '-';
        if ($employee && $employee->date_birth) {
            $age = Carbon::parse($employee->date_birth)->age . ' Tahun';
        }

        // Jenis Kelamin
        $gender = '-';
        if ($employee && $employee->gender) {
            $gender = match (strtolower(trim($employee->gender))) {
                'male', 'l', 'laki-laki' => 'Laki-laki',
                'female', 'p', 'perempuan' => 'Perempuan',
                default => $employee->gender
            };
        }

        // Tanggal Surat (Winuri, dd MMMM yyyy)
        $date = $result->reviewed_at ? Carbon::parse($result->reviewed_at) : Carbon::now();
        $formattedDate = $date->translatedFormat('d F Y');

        // Target deadline rujukan
        $followUpDateText = $result->follow_up_date 
            ? Carbon::parse($result->follow_up_date)->translatedFormat('d F Y') 
            : null;

        // Diagnosa (dari kategori penyakit temuan atau catatan dokter)
        $diagnosaList = $result->diseaseCategories->pluck('name')->toArray();
        $diagnosaText = !empty($diagnosaList) ? implode(', ', $diagnosaList) : 'Dalam proses evaluasi medis';

        return view('pdf.mcu_specialist_referral', [
            'result'           => $result,
            'employee'         => $employee,
            'age'              => $age,
            'gender'           => $gender,
            'formattedDate'    => $formattedDate,
            'followUpDateText' => $followUpDateText,
            'diagnosaText'     => $diagnosaText,
            'logoMsm'          => $logoMsm,
            'logoArchi'        => $logoArchi,
        ])->render();
    }

    /**
     * Render PDF resmi Formulir Rujukan Medis TT-OHS-FRO-028D.
     */
    public static function renderPdf(McuResult $result)
    {
        $html = self::generateHtml($result);

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'defaultFont'          => 'Times New Roman'
        ]);

        return $pdf;
    }
}
