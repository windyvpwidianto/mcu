<?php

namespace App\Livewire\Dashboard\Hazard;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Hazard as ModelsHazard;

class HazardTrandChart extends Component
{
    public $data;
    public $start_date;
    public $end_date;
    public $years;
    public function mount()
    {
        // Ambil tanggal paling akhir dari database
        $lastDateRaw = ModelsHazard::max('tanggal');

        if ($lastDateRaw) {
            $lastDate = Carbon::parse($lastDateRaw);

            // End date adalah tanggal terakhir tersebut
            $this->end_date = $lastDate->format('d-m-Y');

            // Start date adalah 11 bulan sebelumnya (total 12 bulan termasuk bulan terakhir)
            // Kita gunakan startOfMonth agar range-nya rapi mencakup satu bulan penuh
            $this->start_date = $lastDate->copy()->subMonths(11)->startOfMonth()->format('d-m-Y');
        } else {
            // Fallback jika database masih kosong
            $this->end_date = now()->format('d-m-Y');
            $this->start_date = now()->subMonths(11)->startOfMonth()->format('d-m-Y');
        }

        $this->loadData();
    }
    #[On('dateRangeUpdated')]
    public function updatedDateRange($data)
    {
        if (!empty($data['start']) && !empty($data['end'])) {
            // Jika user memilih tanggal manual
            $this->start_date = $data['start'];
            $this->end_date   = $data['end'];
            $this->years      = null;
        } else {
            // Jika filter dihapus, kembalikan ke logika 12 bulan berjalan dari data terakhir
            $lastDateRaw = ModelsHazard::max('tanggal');
            $lastDate = $lastDateRaw ? Carbon::parse($lastDateRaw) : Carbon::now();

            $this->end_date   = $lastDate->format('d-m-Y');
            $this->start_date = $lastDate->copy()->subMonths(11)->startOfMonth()->format('d-m-Y');

            // Pastikan years di-null agar query loadData fokus pada range tanggal
            $this->years = null;
        }

        $this->loadData();
    }

    public function loadData()
    {
        // 1. Inisialisasi Base Query (Kriteria Filter Utama) - Tetap sama
        $baseQuery = ModelsHazard::query()
            ->when($this->start_date && $this->end_date, function ($q) {
                return $q->dateRange($this->start_date, $this->end_date);
            })
            ->when(!$this->start_date || !$this->end_date, function ($q) {
                $yearFilter = $this->years ?? now()->subMonth()->year;
                return $q->whereYear('tanggal', $yearFilter);
            });

        // 2. Ambil Data Statistik (DITAMBAHKAN YEAR agar urutan kronologis benar)
        $chartStats = (clone $baseQuery)
            ->selectRaw('YEAR(tanggal) as year, MONTH(tanggal) as month, COUNT(*) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // 3. Format data untuk Chart (Disesuaikan agar mengambil dari koleksi yang memiliki year)
        $data = [
            'months' => $chartStats->map(function ($stat) {
                // Menghasilkan format "Jan 25" atau "Jan" tergantung kebutuhan Anda
                return Carbon::create($stat->year, $stat->month, 1)->format('M Y');
            })->toArray(),
            'counts' => $chartStats->pluck('total')->toArray()
        ];

        // 4. Simpan ke property dan Dispatch - Tetap sama
        $this->data = json_encode($data);
        $this->dispatch('trandChart', $this->data);
    }

    public function render()
    {
        // Opsional: Jika loadData berat, pertimbangkan memanggilnya hanya saat filter berubah
        $this->loadData();
        return view('livewire.dashboard.hazard.hazard-trand-chart');
    }
}
