<?php

namespace App\Livewire\Inspection;

use App\Models\FireProtection;
use App\Models\InspectionChecklist;
use App\Models\InspectionSession;
use App\Models\Location;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class FireInspectionList extends Component
{
    use WithPagination;

    public $selectedItems = [];
    public $selectAll = false;
    public $fields = [];
    public $type;
    public $date;
    public $date_export;
    public $area;
    public $search_type = '';
    public $location_id;
    public $show_location = false;
    public $locations = [];
    public $searchLocation = '';

    public function updatingSearchType()
    {
        $this->resetPage();
    }
    public function updatingLocationId()
    {
        $this->resetPage();
    }
    public function updatingDate()
    {
        $this->resetPage();
    }
    public function updatingDateExport()
    {
        $this->resetPage();
    }
    public function clear_filter()
    {
        $this->reset('search_type', 'location_id', 'date', 'area', 'searchLocation');
    }
    public function updatedSearchLocation()
    {
        $this->location_id = null; // Reset location_id saat searchLocation berubah
        if (strlen($this->searchLocation) > 2) {
            $this->locations = Location::where('name', 'like', '%' . $this->searchLocation . '%')
                ->orderBy('name')
                ->limit(50)
                ->get();
            $this->show_location = true;
        } else {
            $this->locations = [];
            $this->show_location = false;
        }
    }
    public function getChecklistFromDB()
    {
        $master = DB::table('inspection_checklists')
            ->where('equipment_type', $this->type)
            ->where(function ($q) {
                $q->where('location_keyword', 'Default')
                    ->orWhereRaw('? LIKE CONCAT("%", location_keyword, "%")', [$this->searchLocation]);
            })
            // Mengambil yang lokasi spesifik (seperti Maesa Camp) dulu, baru Default
            ->orderByRaw("CASE WHEN location_keyword = 'Default' THEN 2 ELSE 1 END")
            ->first();

        if ($master) {
            $this->fields[$this->type] = [
                'inputs' => json_decode($master->inputs, true),
                'checks' => json_decode($master->checks, true),
            ];
        }
    }
    public function selectLocation($id, $name)
    {
        $this->location_id = $id;
        $this->searchLocation = $name;
        $this->area = $name;
        $this->show_location = false;
        $this->getChecklistFromDB();
    }
    public function updatedType($value)
    {
        $this->type = $value;
        // Logika Khusus untuk Maesa Camp
        $this->getChecklistFromDB();
        // Jika area sudah terpilih, refresh daftar alat di area tersebut
        if ($this->location_id) {
            $this->selectLocation($this->location_id, $this->searchLocation);
        }
    }
    // Tambahkan ini di dalam class FireInspectionList
    public function getInspectionsProperty()
    {
        return FireProtection::query()
            ->searchByType($this->search_type)
            ->searchByLocation($this->location_id)
            ->searchInstectionsByDate($this->date)
            ->get(); // Gunakan get() bukan paginate() untuk ambil semua ID yang terfilter
    }
    // Fungsi Logika Select All
    public function updatedSelectAll($value)
    {
        if ($value) {
            // Ambil semua ID dari hasil query inspeksi saat ini
            $this->selectedItems = $this->getInspectionsProperty()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    // Fungsi Hapus Masal
    public function deleteSelected()
    {
        if (empty($this->selectedItems)) return;

        // 1. Ambil data inspection beserta relasi session-nya
        $inspections = FireProtection::with('inspectionSession')
            ->whereIn('id', $this->selectedItems)
            ->get();

        // 2. Kumpulkan ID Session unik yang terlibat dalam penghapusan ini
        $sessionIds = $inspections->pluck('inspection_session_id')->unique();

        foreach ($inspections as $inspection) {
            // --- A. Hapus Dokumentasi Produk (Selalu dihapus karena milik pribadi item) ---
            if ($inspection->documentation_path && Storage::disk('public')->exists($inspection->documentation_path)) {
                Storage::disk('public')->delete($inspection->documentation_path);
            }
        }

        // --- B. Logika Hapus Foto Area (Hanya jika SEMUA item di session tersebut dihapus) ---
        foreach ($sessionIds as $sessionId) {
            $session = InspectionSession::find($sessionId);

            if ($session && $session->area_photo_path) {
                // Hitung total item yang ada di database untuk session ini
                $totalItemsInDb = FireProtection::where('inspection_session_id', $sessionId)->count();

                // Hitung berapa banyak item dari session ini yang sedang dipilih untuk dihapus
                $itemsToDeleteCount = $inspections->where('inspection_session_id', $sessionId)->count();

                // JIKA jumlah yang dihapus SAMA DENGAN total yang ada di DB
                if ($itemsToDeleteCount === $totalItemsInDb) {
                    // Hapus file fisik foto area
                    if (Storage::disk('public')->exists($session->area_photo_path)) {
                        Storage::disk('public')->delete($session->area_photo_path);
                    }

                    // Set path di database jadi null (opsional jika session-nya tidak ikut dihapus)
                    $session->delete();
                }
            }
        }

        // 3. Eksekusi hapus data dari database
        FireProtection::whereIn('id', $this->selectedItems)->delete();

        // Reset UI State
        $this->selectedItems = [];
        $this->selectAll = false;

        $this->dispatch('alert', ['text' => "Data berhasil dibersihkan!"]);
    }

    public function exportPDF()
    {
        // Gunakan Carbon untuk mengambil angka Bulan dan Tahun saja
        $inspections = FireProtection::query()
            ->select('fire_protections.*')
            ->join('inspection_sessions', 'fire_protections.inspection_session_id', '=', 'inspection_sessions.id')
            ->with(['equipmentMaster.location', 'inspectionSession'])
            ->searchByType($this->type)
            ->searchByLocation($this->location_id)
            ->searchInstectionsByMonth($this->date_export)
            ->latest('inspection_sessions.inspection_date') // Mengganti orderBy desc
            ->distinct()
            ->get();

        if ($inspections->isEmpty()) {
            $this->dispatch('alert', [
                'text' => "Tidak ada data {$this->type} untuk periode " . Carbon::parse($this->date)->locale('id')->translatedFormat('F Y'),
                'backgroundColor' => "background: linear-gradient(135deg, #f44336, #d32f2f);",
            ]);
            return;
        }

        $structure = $this->fields[$this->type] ?? null;

        // 1. Load View
        $pdf = Pdf::loadView('pdf.dynamic-report', [
            'data' => $inspections,
            'type' => $this->type,
            'area' => $inspections->first()->equipmentMaster->location->name ?? 'N/A',
            'structure' => $structure,
            'month' => Carbon::parse($inspections->first()->inspectionSession->inspection_date)->locale('id')->translatedFormat('F Y'),
            'tgl' => Carbon::parse($inspections->first()->inspectionSession->inspection_date)->locale('id')->translatedFormat('d, F Y'),
            'submitted_by' => $inspections->first()->inspectionSession->submitted_by ?? $inspections->first()->submitted_by,
            'inspection_number' => $inspections->first()->inspectionSession->inspection_number ?? 'N/A',
        ])->setPaper('a4', 'landscape');

        // 2. Render PDF terlebih dahulu agar bisa mengakses Canvas
        $pdf->render();

        // 3. Ambil Canvas untuk menambahkan penomoran halaman
        $canvas = $pdf->getCanvas();
        $font = null; // Ini akan otomatis menggunakan font default PDF (Helvetica/Times-Roman)
        $size = 9;

        /**
         * Parameter page_text:
         * (X, Y, Text, Font, Size, Color)
         * Untuk Landscape A4: X = 730 (Kanan), Y = 560 (Bawah)
         */
        $canvas->page_text(730, 560, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, $size, [0, 0, 0]);

        $filename = "Rekap_Inspeksi_" . str_replace(' ', '_', $this->type) . "_" . Carbon::now()->format('m_Y') . ".pdf";

        // 4. Download menggunakan output yang sudah dimodifikasi canvasnya
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }
    public function render()
    {
        return view('livewire.inspection.fire-inspection-list', [
            'availableTypes' => InspectionChecklist::distinct()->pluck('equipment_type'),
            'inspections' => FireProtection::query()
                ->select('fire_protections.*') // Pastikan ambil kolom milik fire_protections
                ->join('inspection_sessions', 'fire_protections.inspection_session_id', '=', 'inspection_sessions.id')
                ->with('equipmentMaster.location', 'inspectionSession')
                ->searchByType($this->search_type)
                ->searchByLocation($this->location_id)
                ->searchInstectionsByDate($this->date)
                ->orderBy('inspection_sessions.inspection_date', 'desc') // Urutkan berdasarkan tgl sesi
                ->paginate(10),
        ]);
    }
    public function isFormIncomplete(): bool
    {
        // Mengembalikan true jika ada salah satu field yang kosong
        return empty($this->type) || empty($this->date_export) || empty($this->location_id);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
