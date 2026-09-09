<?php

namespace App\Livewire\Dashboard\Hazard;

use Carbon\Carbon;
use App\Models\Hazard;
use Livewire\Component;
use Livewire\Attributes\On;

class HazardUserReport extends Component
{
    public $pelapor; // nama department atau contractor
    public $start_date;
    public $end_date;
    public $years;
    // Trigger awal saat komponen dimuat
    public function mount()
    {
        // Ambil tanggal paling akhir dari database
        $lastDateRaw = Hazard::max('tanggal');

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
            $lastDateRaw = Hazard::max('tanggal');
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
        // 1. Ambil data hazard dengan relasi pelapor dan filter range yang konsisten
        $hazards = Hazard::with('pelapor')
            ->when($this->start_date && $this->end_date, function ($q) {
                // Gunakan scope dateRange yang sudah kita buat
                return $q->dateRange($this->start_date, $this->end_date);
            })
            ->when(!$this->start_date || !$this->end_date, function ($q) {
                // Fallback: Jika range kosong, gunakan filter years atau tahun dari bulan lalu
                $yearFilter = $this->years ?? now()->subMonth()->year;
                return $q->whereYear('tanggal', $yearFilter);
            })
            ->get();

        // 2. Kumpulkan kategori berdasarkan nama pelapor
        $grouped = $hazards->groupBy(function ($hazard) {
            return $hazard->pelapor ? $hazard->pelapor->name : 'Tidak Diketahui';
        });

        // 3. Hitung jumlah laporan per orang, urutkan terbesar ke terkecil, dan ambil TOP 10
        $counts = $grouped->map->count()
            ->sortDesc()
            ->take(10);

        // 4. Format data untuk Chart (menggunakan 'range' agar lebih informatif daripada sekedar 'year')
        $value = [
            'year'  => ($this->start_date && $this->end_date)
                ? $this->start_date . ' s/d ' . $this->end_date
                : ($this->years ?? now()->year),
            'label'  => $counts->keys()->values()->toArray(), // Nama-nama pelapor top 10
            'counts' => $counts->values()->toArray(),
            'range'  => ($this->start_date && $this->end_date)
                ? \Carbon\Carbon::parse($this->start_date)->format('d M Y') . " - " . \Carbon\Carbon::parse($this->end_date)->format('d M Y')
                : "Tahun $this->years"         // Jumlah laporan mereka
        ];

        // 5. Simpan ke property dan Dispatch ke frontend
        $this->pelapor = json_encode($value);
        $this->dispatch('distribusiPelapor', $this->pelapor);
    }
    public function render()
    {
        $this->loadData();
        return view('livewire.dashboard.hazard.hazard-user-report');
    }
}
