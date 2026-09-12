<?php

namespace App\Livewire\Mcu;

use App\Models\User;
use App\Models\McuRecord;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class EmployeeMcuDetail extends Component
{
    public $employeeId;
    public $employee;
    
    // Form fields for new MCU Record
    public $showAddModal = false;
    public $mcu_year;
    public $mcu_date;
    public $status = 'Completed';
    public $medical_status = 'fit_to_work'; // from McuResult

    public function mount($employeeId)
    {
        $this->employeeId = $employeeId;
        $this->employee = User::findOrFail($employeeId);
        $this->mcu_year = date('Y');
        $this->mcu_date = date('Y-m-d');
    }

    public function openAddModal()
    {
        $this->reset(['mcu_year', 'mcu_date', 'status', 'medical_status']);
        $this->mcu_year = date('Y');
        $this->mcu_date = date('Y-m-d');
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
    }

    public function saveMcuRecord()
    {
        $this->validate([
            'mcu_year' => 'required|numeric',
            'mcu_date' => 'required|date',
            'status' => 'required|string',
            'medical_status' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Create record
            $record = McuRecord::create([
                'employee_id' => $this->employeeId,
                'mcu_year' => $this->mcu_year,
                'mcu_date' => $this->mcu_date,
                'status' => $this->status,
                'notification_status' => 'notified',
            ]);

            // Create result skeleton
            $record->result()->create([
                'status' => $this->medical_status,
                'workflow_status' => 'reviewed',
            ]);

            DB::commit();

            $this->closeAddModal();
            $this->dispatch('alert', [
                'text' => 'Riwayat MCU berhasil ditambahkan.',
                'duration' => 3000,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', [
                'text' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'duration' => 5000,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #f59e0b, #ef4444)",
            ]);
        }
    }

    public function render()
    {
        // Load employee with records ordered by year descending
        $this->employee->load(['mcuRecords' => function($q) {
            $q->orderBy('mcu_year', 'desc')->orderBy('mcu_date', 'desc');
        }, 'mcuRecords.result']);

        return view('livewire.mcu.employee-mcu-detail');
    }
}
