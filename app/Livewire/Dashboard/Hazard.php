<?php

namespace App\Livewire\Dashboard;

use App\Models\Hazard as ModelsHazard;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Hazard extends Component
{
    public $total_hazard;
    public $range_date = '';
    public $start_date;
    public $end_date;
    public function updatedRangeDate($value)
    {
        // Cek apakah nilai tidak kosong
        if (!empty($value)) {
            // Pisahkan string berdasarkan " to "
            $dates = explode(' Ke ', $value);

            // Pastikan ada dua tanggal yang valid
            if (count($dates) === 2) {
                $this->start_date = $dates[0];
                $this->end_date = $dates[1];
                $this->dispatch('dateRangeUpdated', ['start' => $this->start_date,'end'   => $this->end_date,]);
            }
        } else {
            $this->reset('start_date', 'end_date');
            $this->dispatch('dateRangeUpdated', [
                'start' => null,
                'end'   => null,
            ]);
        }
    }
    public function clearFilter(){
        $this->reset('range_date','start_date','end_date');
    }
    public function render()
    {
        $totalHazard = ModelsHazard::when($this->start_date && $this->end_date, function ($q) {
            $q->dateRange($this->start_date, $this->end_date);
        })->count();

        $hazardByStatus = ModelsHazard::when($this->start_date && $this->end_date, function ($q) {
            $q->dateRange($this->start_date, $this->end_date);
        });
        $statusHazard = $hazardByStatus->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status')->toArray();
        return view('livewire.dashboard.hazard', [
            'totalHazard' => $totalHazard,
            'hazardByStatus' => $statusHazard,
            'latestHazardReports' => ModelsHazard::when($this->start_date && $this->end_date, function ($q) {
                $q->dateRange($this->start_date, $this->end_date);
            })->latest()->limit(5)->get(),
        ]);
    }
}
