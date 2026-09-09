<?php

namespace App\Livewire\Dashboard\Hazard;

use Carbon\Carbon;
use App\Models\Hazard;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

class HazardDistribusiStatus extends Component
{
    public $statusChart;
    public $start_date;
    public $end_date;
    public $years;

    public function mount()
    {
        $lastDateRaw = Hazard::max('tanggal');

        if ($lastDateRaw) {
            $lastDate = Carbon::parse($lastDateRaw);

            // 2. Set end_date ke tanggal terbaru
            $this->end_date = $lastDate->format('d-m-Y');

            // 3. Set start_date ke 12 bulan sebelumnya dari tanggal terbaru
            // Gunakan startOfMonth agar mencakup bulan penuh jika diinginkan
            $this->start_date = $lastDate->copy()->subMonths(11)->startOfMonth()->format('d-m-Y');
        } else {
            // Fallback jika database kosong
            $this->start_date = now()->subMonths(11)->startOfMonth()->format('d-m-Y');
            $this->end_date = now()->format('d-m-Y');
        }
        $this->loadData();
    }
    #[On('dateRangeUpdated')]
    public function updatedDateRange($data)
    {
        // Cek apakah data start dan end tersedia dan tidak kosong
        if (!empty($data['start']) && !empty($data['end'])) {
            $this->start_date = $data['start'];
            $this->end_date   = $data['end'];

            // Opsional: Jika menggunakan range, mungkin Anda ingin mereset filter tahun
            // agar tidak bentrok dengan filter tanggal spesifik
            $this->years = null;
        } else {
            // Kondisi jika filter tanggal dihapus (Kosong)
            $this->start_date = null;
            $this->end_date   = null;

            // Set tahun ke tahun dari bulan lalu
            $this->years = Carbon::now()->subMonth()->year;
        }

        $this->loadData();
    }
    public function loadData()
    {
        // 1. Definisikan Filter Utama (Base Query)
        $baseQuery = Hazard::query()
            ->when($this->start_date && $this->end_date, function ($q) {
                return $q->dateRange($this->start_date, $this->end_date);
            })
            ->when(!$this->start_date || !$this->end_date, function ($q) {
                $yearFilter = $this->years ?? now()->subMonth()->year;
                return $q->whereYear('tanggal', $yearFilter);
            });

        // 2. Query untuk List Data (Jika butuh ditampilkan di tabel)
        // $this->hazards = (clone $baseQuery)->with(['department', 'contractor'])->latest('tanggal')->get();

        // 3. Query untuk Grafik Distribusi Status (Gunakan clone agar filter tetap terbawa)
        $statusStats = (clone $baseQuery)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        // 4. Format data untuk Chart (Labels & Values)
        $value = [
            'labels' => $statusStats->pluck('status')->toArray(),
            'values' => $statusStats->pluck('total')->toArray(),
        ];

        $this->statusChart = json_encode($value);

        // 5. Kirim data ke Browser
        $this->dispatch('distribusiStatus', $this->statusChart);
    }

    public function render()
    {
        // loadData dipanggil di sini agar setiap ada perubahan filter (start_date/years)
        // grafik langsung ter-update otomatis.
        $this->loadData();
        return view('livewire.dashboard.hazard.hazard-distribusi-status');
    }
}
