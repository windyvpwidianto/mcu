<?php

namespace App\Livewire\Mcu;

use App\Models\DiseaseCategory; // <-- 1. Jangan lupa import model ini
use App\Models\McuRecord;
use App\Models\McuResult;
use Carbon\Carbon;
use Livewire\Component;

class McuDashboard extends Component
{
    public function render()
    {
        $today = Carbon::now()->toDateString();

        // --- CODE KAMU SEBELUMNYA (Kehadiran, Fit Status, Workflow) ---
        $sudahMcu = McuRecord::has('result')->count();

        $terlewatMcu = McuRecord::whereDoesntHave('result')
            ->whereHas('schedule', function ($query) use ($today) {
                $query->whereDate('schedule_date', '<', $today);
            })->count();

        $menungguJadwal = McuRecord::whereDoesntHave('result')
            ->whereHas('schedule', function ($query) use ($today) {
                $query->whereDate('schedule_date', '>=', $today);
            })->count();

        $fitStatusRaw = McuResult::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $fitStatus = [
            'fit_to_work'     => $fitStatusRaw['fit_to_work'] ?? 0,
            'fit_with_notes'  => $fitStatusRaw['fit_with_notes'] ?? 0,
            'temporary_unfit' => $fitStatusRaw['temporary_unfit'] ?? 0,
            'unfit'           => $fitStatusRaw['unfit'] ?? 0,
        ];

        $workflowStatusRaw = McuResult::selectRaw('workflow_status, count(*) as total')
            ->groupBy('workflow_status')
            ->pluck('total', 'workflow_status')
            ->toArray();

        $workflowStatus = [
            'pending_doctor' => $workflowStatusRaw['pending_doctor'] ?? 0,
            'reviewed'       => $workflowStatusRaw['reviewed'] ?? 0,
        ];

        // --- 2. TAMBAHAN BARU: DATA TOP PENYAKIT ---
        // Mengambil 10 penyakit terbanyak yang memiliki relasi ke mcu_results
        $topDiseases = DiseaseCategory::withCount('mcuResults')
            ->has('mcuResults') // Hanya ambil yang ada kasusnya
            ->orderBy('mcu_results_count', 'desc') // Ambil 10 terbanyak
            ->limit(10)
            ->get()
            ->sortBy('mcu_results_count'); // di-asc agar grafik horisontal urut dari terbesar di atas

        $diseaseNames  = $topDiseases->pluck('name')->toArray();
        $diseaseCounts = $topDiseases->pluck('mcu_results_count')->toArray();

        return view('livewire.mcu.mcu-dashboard', compact(
            'sudahMcu',
            'terlewatMcu',
            'menungguJadwal',
            'fitStatus',
            'workflowStatus',
            'diseaseNames',   // <-- 3. Kirim ke view
            'diseaseCounts'   // <-- 4. Kirim ke view
        ));
    }
}
