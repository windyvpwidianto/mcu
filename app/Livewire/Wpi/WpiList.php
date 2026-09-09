<?php

namespace App\Livewire\Wpi;

use Livewire\Component;
use App\Models\WpiReport;
use App\Models\Contractor;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;

class WpiList extends Component
{
    use WithPagination;

    public $search = '';

    // Reset halaman saat user mengetik di kolom pencarian
    public function updatingSearch()
    {
        $this->resetPage();
    }
     public function getRandomBadgeColor($status)
    {
      $map = [
        'Cancelled'   => 'badge-error',   // Tulis lengkap
        'Closed'      => 'badge-success',
        'Review Event' => 'badge-warning',
        'Final Review'     => 'badge-accent',
        'Assigned'   => 'badge-info',
        'Submitted'   => 'badge-info',
    ];
      return $map[$status] ?? 'badge-neutral';
    }
    public function deleteReport($id)
    {
        $report = WpiReport::with('findings')->find($id);

        if ($report) {
            // Loop setiap temuan untuk menghapus foto fisik
            foreach ($report->findings as $finding) {
                // Hapus foto temuan utama
                if ($finding->photos) {
                    foreach ($finding->photos as $path) {
                        \App\Helpers\FileHelper::deleteFile($path);
                    }
                }
                // Hapus foto tindakan pencegahan
                if ($finding->photos_prevention) {
                    foreach ($finding->photos_prevention as $path) {
                        \App\Helpers\FileHelper::deleteFile($path);
                    }
                }
            }

            // Hapus data laporan (cascade delete ke findings jika diatur di migrasi)
            $report->delete();

            $this->dispatch('alert', [
                'text' => 'Laporan berhasil dihapus secara permanen.',
                'backgroundColor' => "linear-gradient(to right, #ef4444, #991b1b)",
            ]);
        }
    }
    public function exportPDF($id)
    {
        $report = WpiReport::with(['findings'])->findOrFail($id);
        $no_referensi = $report->no_referensi;
        $isContractor = Contractor::where('contractor_name', $report->department)->exists();
        $deptLabel = $isContractor ? 'Contractor' : 'Department';
        $pdf = Pdf::loadView('pdf.wpi-report', compact('report', 'deptLabel', 'no_referensi'))
            ->setOption([
                'isPhpEnabled' => true,
                'isRemoteEnabled' => true
            ])
            ->setPaper('a4', 'portrait');
        // 2. Render PDF terlebih dahulu agar bisa mengakses Canvas
        $pdf->render();
        $canvas = $pdf->getCanvas();
        $font = null; // Ini akan otomatis menggunakan font default PDF (Helvetica/Times-Roman)
        $size = 9;
        /**
         * Parameter page_text:
         * (X, Y, Text, Font, Size, Color)
         * Untuk Landscape A4: X = 730 (Kanan), Y = 560 (Bawah)
         */
        $canvas->page_text(455, 788, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, $size, [0, 0, 0]);
        return response()->streamDownload(function () use ($pdf) {
            // Menggunakan output() memastikan seluruh script penomoran diproses
            echo $pdf->output();
        }, "Laporan_WPI.pdf");
    }
    public function render()
    {
        // Query dengan pencarian pada departemen atau lokasi
        $reports = WpiReport::query()->where('department', 'like', '%' . $this->search . '%')
            ->orderBy('report_date', 'desc')
            ->paginate(10);

        return view('livewire.wpi.wpi-list', [
            'reports' => $reports
        ]);
    }
}
