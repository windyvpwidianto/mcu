<?php

namespace App\Livewire\Dashboard\Hazard;

use App\Models\Hazard;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class EnvHazard extends Component
{
    public $start_date;
    public $end_date;
    public $chartJenisBahaya;
    public $chartKtaTta; // Tambahkan variabel untuk Pie Chart
    public $years;

    public function mount()
    {
        $lastDateRaw = Hazard::max('tanggal');
        if ($lastDateRaw) {
            $lastDate = Carbon::parse($lastDateRaw);
            $this->end_date = $lastDate->format('d-m-Y');
            $this->start_date = $lastDate->copy()->subMonths(11)->startOfMonth()->format('d-m-Y');
        } else {
            $this->end_date = now()->format('d-m-Y');
            $this->start_date = now()->subMonths(11)->startOfMonth()->format('d-m-Y');
        }

        $this->loadData();
    }

    #[On('dateRangeUpdated')]
    public function updatedDateRange($data)
    {
        if (!empty($data['start']) && !empty($data['end'])) {
            $this->start_date = $data['start'];
            $this->end_date   = $data['end'];
            $this->years      = null;
        } else {
            $lastDateRaw = Hazard::max('tanggal');
            $lastDate = $lastDateRaw ? Carbon::parse($lastDateRaw) : Carbon::now();
            $this->end_date   = $lastDate->format('d-m-Y');
            $this->start_date = $lastDate->copy()->subMonths(11)->startOfMonth()->format('d-m-Y');
            $this->years = null;
        }

        $this->loadData();
    }

    public function loadData()
    {
        // 1. Base Query Filter
        $query = Hazard::query()
            ->whereHas('eventSubType', function ($q) {
                $q->byEventType('ENV Hazard Report');
            })
            ->when($this->start_date && $this->end_date, function ($q) {
                $q->dateRange($this->start_date, $this->end_date);
            })
            ->when(!$this->start_date || !$this->end_date, function ($q) {
                $q->whereYear('tanggal', $this->years);
            });

        // --- A. LOGIKA BAR CHART (JENIS BAHAYA) ---
        $rawData = (clone $query)
            ->with(['eventSubType'])
            ->select(
                DB::raw("DATE_FORMAT(tanggal, '%b %Y') as bulan_tahun"),
                'event_sub_type_id',
                DB::raw('count(*) as total'),
                DB::raw("LAST_DAY(tanggal) as urutan_bulan")
            )
            ->groupBy('bulan_tahun', 'event_sub_type_id', 'urutan_bulan')
            ->orderBy('urutan_bulan')
            ->get();

        $months = $rawData->pluck('bulan_tahun')->unique()->values()->toArray();
        $jenisLabels = $rawData->map(function ($item) {
            return $item->eventSubType->event_sub_type_name ?? 'N/A';
        })->unique()->values();

        $series = [];
        foreach ($jenisLabels as $jenis) {
            $dataPoint = [];
            foreach ($months as $month) {
                $match = $rawData->first(function ($item) use ($month, $jenis) {
                    return $item->bulan_tahun === $month &&
                        ($item->eventSubType->event_sub_type_name ?? 'N/A') === $jenis;
                });
                $dataPoint[] = $match ? (int)$match->total : 0;
            }

            $series[] = [
                'name' => $jenis,
                'type' => 'bar',
                'barMaxWidth' => 20,
                'barGap' => '15%',
                'barCategoryGap' => '35%',
                'label' => [
                    'show' => true,
                    'position' => 'top',
                    'distance' => 5,
                    'fontSize' => 10,
                ],
                'itemStyle' => [
                    'borderRadius' => [3, 3, 0, 0]
                ],
                'data' => $dataPoint,
            ];
        }

        $this->chartJenisBahaya = json_encode([
            'labels' => $months,
            'series' => $series,
            'legend' => $jenisLabels->toArray(),
            'range'  => ($this->start_date && $this->end_date)
                ? \Carbon\Carbon::parse($this->start_date)->format('d M Y') . " - " . \Carbon\Carbon::parse($this->end_date)->format('d M Y')
                : "Tahun $this->years"
        ]);

        // --- B. LOGIKA PIE CHART (KTA VS TTA) ---
        $pieDataRaw = (clone $query)
            ->select('key_word', DB::raw('count(*) as total'))
            // Pastikan key_word sesuai dengan isi database (Kondisi Tidak Aman / Tindakan Tidak Aman)
            ->whereIn('key_word', ['kta', 'tta'])
            ->groupBy('key_word')
            ->get();

        // Map data dengan Label yang lebih rapi untuk tampilan Chart
        $pieSeriesData = $pieDataRaw->map(function ($item) {
            $label = ($item->key_word === 'kta') ? __('Kondisi Tidak Aman')
                : __('Tindakan Tidak Aman');
            return [
                'name' => $label,
                'value' => (int)$item->total
            ];
        })->toArray();

        $this->chartKtaTta = json_encode([
            'series' => $pieSeriesData
        ]);

        // Dispatch Event ke Browser
        $this->dispatch('updateEnvJenisBahayaChart', $this->chartJenisBahaya);
        $this->dispatch('updateEnvPieChart', $this->chartKtaTta);
    }
    public function render()
    {
        return view('livewire.dashboard.hazard.env-hazard');
    }
}
