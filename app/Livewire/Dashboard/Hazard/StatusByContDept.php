<?php

namespace App\Livewire\Dashboard\Hazard;

use Carbon\Carbon;
use App\Models\Hazard;
use Livewire\Component;
use Livewire\Attributes\On;

class StatusByContDept extends Component
{
    public $start_date;
    public $end_date;
    public $statusDeptCont;
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
        $hazards = Hazard::with(['department', 'contractor'])
            ->when($this->start_date && $this->end_date, function ($q) {
                $q->dateRange($this->start_date, $this->end_date);
            })->when(!$this->start_date || !$this->end_date, function ($q) {
                $q->whereYear('tanggal', $this->years);
            })->get();

        // 1. Ambil label unik (Dept + Contractor)
        $deptNames = $hazards->whereNotNull('department_id')->pluck('department.department_name')->unique();
        $contNames = $hazards->whereNotNull('contractor_id')->pluck('contractor.contractor_name')->unique();
        $labels = $deptNames->merge($contNames)->filter()->unique()->toArray();

        $tempData = [];

        // 2. Hitung status dan simpan sementara untuk sorting
        foreach ($labels as $name) {
            $reportsForLabel = $hazards->filter(function ($report) use ($name) {
                $isDept = ($report->department->department_name ?? '') === $name;
                $isCont = ($report->contractor->contractor_name ?? '') === $name;
                return $isDept || $isCont;
            });

            $closed = $reportsForLabel->where('status', 'closed')->count();
            $open = $reportsForLabel->whereNotIn('status', ['closed', 'cancel'])->count();

            $tempData[] = [
                'label' => $name,
                'closed' => $closed,
                'open' => $open,
                'total' => $closed + $open
            ];
        }

        // 3. URUTKAN: Dari total terbesar ke terkecil
        usort($tempData, fn($a, $b) => $b['total'] <=> $a['total']);

        // 4. Pecah kembali menjadi format chartData
        $chartData = [
            'labels' => array_column($tempData, 'label'),
            'closed' => array_column($tempData, 'closed'),
            'open'   => array_column($tempData, 'open'),
            'range'  => ($this->start_date && $this->end_date)
                ? \Carbon\Carbon::parse($this->start_date)->format('d M Y') . " - " . \Carbon\Carbon::parse($this->end_date)->format('d M Y')
                : "Tahun $this->years"
        ];

        $this->statusDeptCont = json_encode($chartData);
        $this->dispatch('hazardStatus_DeptOrCont', $this->statusDeptCont);
    }

    public function render()
    {
        $this->loadData();
        return view('livewire.dashboard.hazard.status-by-cont-dept');
    }
}
