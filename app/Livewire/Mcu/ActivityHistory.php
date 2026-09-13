<?php

namespace App\Livewire\Mcu;

use App\Models\User;
use App\Models\McuRecord;
use App\Models\McuResult;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class ActivityHistory extends Component
{
    public $employeeId;
    public $employee;
    public $isOpen = false;

    protected $listeners = ['openMcuHistory' => 'openModal'];

    public function mount($employeeId = null)
    {
        if ($employeeId) {
            $this->employeeId = $employeeId;
            $this->employee = User::find($employeeId);
        }
    }

    public function openModal($employeeId)
    {
        $this->employeeId = $employeeId;
        $this->employee = User::find($employeeId);
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        $activities = collect();

        if ($this->employeeId) {
            // Find all MCU Records for this employee
            $recordIds = McuRecord::where('employee_id', $this->employeeId)->pluck('id')->toArray();
            
            // Find all MCU Results for those records
            $resultIds = McuResult::whereIn('mcu_record_id', $recordIds)->pluck('id')->toArray();

            // Fetch activities for these subjects
            $activities = Activity::where('log_name', 'mcu')
                ->where(function ($query) use ($recordIds, $resultIds) {
                    $query->where(function ($q) use ($recordIds) {
                        $q->where('subject_type', McuRecord::class)
                          ->whereIn('subject_id', $recordIds);
                    })->orWhere(function ($q) use ($resultIds) {
                        $q->where('subject_type', McuResult::class)
                          ->whereIn('subject_id', $resultIds);
                    });
                })
                ->with('causer')
                ->latest()
                ->get();
        }

        return view('livewire.mcu.activity-history', [
            'activities' => $activities
        ]);
    }
}
