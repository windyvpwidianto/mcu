<?php

namespace App\Livewire\Dashboard\Hazard;

use Carbon\Carbon;
use App\Models\Hazard;
use Livewire\Component;
use Livewire\Attributes\On;

class HazardDistribusiDivisi extends Component
{
    public $categories; // nama department atau contractor
    public $start_date;
    public $end_date;
    public $years;
    // Trigger awal saat komponen dimuat
    public function mount()
    {
        // 1. Ambil tanggal paling akhir (Data terbaru)
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
    public function perbaharuiTanggal($data)
    {
        // Cek apakah data start dan end tersedia dan tidak kosong
        if (!empty($data['start']) && !empty($data['end'])) {
            $this->start_date = $data['start'];
            $this->end_date = $data['end'];
            $this->years = null; // Reset filter tahun
        } else {
            // Kondisi jika filter tanggal dihapus oleh user
            $lastDateRaw = Hazard::max('tanggal');
            $lastDate = $lastDateRaw ? Carbon::parse($lastDateRaw) : Carbon::now();

            // Kembalikan ke 12 bulan terakhir
            $this->start_date = $lastDate->copy()->subMonths(11)->startOfMonth()->format('d-m-Y');
            $this->end_date = $lastDate->format('d-m-Y');

            // Opsional: Karena Anda menggunakan range 12 bulan, filter single year mungkin tidak relevan lagi
            $this->years = null;
        }

        $this->loadData();
    }
    public function loadData()
    {
        // 1. Ambil data dengan filter yang konsisten
        $hazards = Hazard::with(['department', 'contractor'])
            // Gunakan range tanggal (baik dari 12 bulan otomatis maupun pilihan user)
            ->when($this->start_date && $this->end_date, function ($q) {
                return $q->dateRange($this->start_date, $this->end_date);
            })
            // Fallback jika start/end_date kosong (misal saat inisialisasi awal sekali)
            ->when(!$this->start_date || !$this->end_date, function ($q) {
                $yearFilter = $this->years ?? now()->subMonth()->year;
                return $q->whereYear('tanggal', $yearFilter);
            })
            ->get();

        // 2. Kumpulkan kategori (Department atau Contractor)
        $grouped = $hazards->groupBy(function ($hazard) {
            if ($hazard->department) {
                return $hazard->department->department_name;
            } elseif ($hazard->contractor) {
                return $hazard->contractor->contractor_name;
            } else {
                return 'Tidak Diketahui';
            }
        });

        // 3. Hitung jumlah per kategori dan urutkan dari terbesar ke terkecil (High to Low)
        $counts = $grouped->map->count()->sortDesc();

        // 4. Format data untuk Chart Distribusi Divisi
        $value = [
            // Jika start_date ada, tampilkan range-nya di title/info chart
            'year'  => ($this->start_date && $this->end_date)
                ? $this->start_date . ' s/d ' . $this->end_date
                : ($this->years ?? now()->year),
            'label'  => $counts->keys()->values()->toArray(),
            'counts' => $counts->values()->toArray(),
            'range'  => ($this->start_date && $this->end_date)
                ? \Carbon\Carbon::parse($this->start_date)->format('d M Y') . " - " . \Carbon\Carbon::parse($this->end_date)->format('d M Y')
                : "Tahun $this->years"
        ];

        $this->categories = json_encode($value);

        // 5. Kirim data ke Browser/Javascript
        $this->dispatch('distribusiDivisi', $this->categories);
    }
    public function render()
    {
        $this->loadData();
        return view('livewire.dashboard.hazard.hazard-distribusi-divisi');
    }
}
