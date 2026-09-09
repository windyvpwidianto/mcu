<?php

namespace App\Livewire\Public;

use Carbon\Carbon;
use App\Models\Hazard;
use App\Models\McuResult;
use App\Models\IncidentReport;
use App\Models\Department;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class HealthStatistics extends Component
{
    public string $selectedYear;
    public array  $availableYears = [];

    public function mount(): void
    {
        $this->selectedYear = (string) now()->year;

        // Bangun daftar tahun dari 3 tahun lalu hingga sekarang
        $currentYear = now()->year;
        $this->availableYears = range($currentYear - 3, $currentYear);
    }

    public function updatedSelectedYear(): void
    {
        // Kosongkan cache ketika tahun berubah agar data ter-reload
        Cache::forget("health_stats_{$this->selectedYear}");
    }

    // ─── COMPUTED PROPERTIES (cached 5 menit) ────────────────────────────────

    /**
     * Ringkasan angka utama (KPI cards)
     */
    public function getKpiSummaryProperty(): array
    {
        return Cache::remember("kpi_summary_{$this->selectedYear}", 300, function () {
            $year = (int) $this->selectedYear;

            $totalHazard   = Hazard::whereYear('tanggal', $year)->count();
            $closedHazard  = Hazard::whereYear('tanggal', $year)->where('status', 'closed')->count();
            $totalIncident = IncidentReport::whereYear('date_time', $year)->count();
            $totalMcu      = McuResult::whereYear('created_at', $year)->count();
            $fitMcu        = McuResult::whereYear('created_at', $year)->where('status', 'fit')->count();

            $hazardCloseRate = $totalHazard > 0 ? round(($closedHazard / $totalHazard) * 100, 1) : 0;
            $mcuFitRate      = $totalMcu    > 0 ? round(($fitMcu    / $totalMcu)    * 100, 1) : 0;

            return [
                'total_hazard'       => $totalHazard,
                'closed_hazard'      => $closedHazard,
                'hazard_close_rate'  => $hazardCloseRate,
                'total_incident'     => $totalIncident,
                'total_mcu'          => $totalMcu,
                'fit_mcu'            => $fitMcu,
                'mcu_fit_rate'       => $mcuFitRate,
            ];
        });
    }

    /**
     * Data untuk Line Chart tren Hazard & Incident per bulan
     */
    public function getMonthlyTrendProperty(): array
    {
        return Cache::remember("monthly_trend_{$this->selectedYear}", 300, function () {
            $year   = (int) $this->selectedYear;
            $months = collect(range(1, 12))->map(fn ($m) => Carbon::create($year, $m)->format('M'));

            $hazardByMonth = Hazard::whereYear('tanggal', $year)
                ->selectRaw('MONTH(tanggal) as month, COUNT(*) as total')
                ->groupBy('month')
                ->pluck('total', 'month');

            $incidentByMonth = IncidentReport::whereYear('date_time', $year)
                ->selectRaw('MONTH(date_time) as month, COUNT(*) as total')
                ->groupBy('month')
                ->pluck('total', 'month');

            $hazardData   = collect(range(1, 12))->map(fn ($m) => $hazardByMonth->get($m, 0))->toArray();
            $incidentData = collect(range(1, 12))->map(fn ($m) => $incidentByMonth->get($m, 0))->toArray();

            return [
                'labels'   => $months->toArray(),
                'hazard'   => $hazardData,
                'incident' => $incidentData,
            ];
        });
    }

    /**
     * Data untuk Donut Chart distribusi hasil MCU
     */
    public function getMcuDistributionProperty(): array
    {
        return Cache::remember("mcu_distribution_{$this->selectedYear}", 300, function () {
            $year = (int) $this->selectedYear;

            $distribution = McuResult::whereYear('created_at', $year)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return [
                'fit'           => (int) ($distribution->get('fit', 0)),
                'unfit'         => (int) ($distribution->get('unfit', 0)),
                'fit_with_note' => (int) ($distribution->get('fit with note', 0)
                                        + $distribution->get('fit_with_note', 0)),
            ];
        });
    }

    /**
     * Data untuk Bar Chart Top 5 Departemen dengan Hazard terbanyak
     */
    public function getTopDepartmentsProperty(): array
    {
        return Cache::remember("top_departments_{$this->selectedYear}", 300, function () {
            $year = (int) $this->selectedYear;

            $data = Hazard::whereYear('tanggal', $year)
                ->whereNotNull('department_id')
                ->with('department:id,department_name')
                ->selectRaw('department_id, COUNT(*) as total')
                ->groupBy('department_id')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            return [
                'labels' => $data->map(fn ($r) => $r->department?->department_name ?? 'N/A')->toArray(),
                'values' => $data->pluck('total')->toArray(),
            ];
        });
    }

    /**
     * Data untuk Bar Chart distribusi Risk Level Hazard
     */
    public function getRiskDistributionProperty(): array
    {
        return Cache::remember("risk_distribution_{$this->selectedYear}", 300, function () {
            $year = (int) $this->selectedYear;

            $data = Hazard::whereYear('tanggal', $year)
                ->whereNotNull('risk_level')
                ->selectRaw('risk_level, COUNT(*) as total')
                ->groupBy('risk_level')
                ->pluck('total', 'risk_level');

            return [
                'low'      => (int) $data->get('low', 0),
                'medium'   => (int) $data->get('medium', 0),
                'high'     => (int) $data->get('high', 0),
                'critical' => (int) $data->get('critical', 0),
            ];
        });
    }

    public function render()
    {
        return view('livewire.public.health-statistics', [
            'kpiSummary'       => $this->kpiSummary,
            'monthlyTrend'     => $this->monthlyTrend,
            'mcuDistribution'  => $this->mcuDistribution,
            'topDepartments'   => $this->topDepartments,
            'riskDistribution' => $this->riskDistribution,
            'availableYears'   => $this->availableYears,
            'selectedYear'     => $this->selectedYear,
        ])->layout('livewire.public.health-layout');
    }
}
