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
    public $attendance_status = 'scheduled';
    public $process_status = 'scheduled';
    public $medical_status = 'fit_to_work'; // from McuResult
    public $doctor_notes = '';

    public $editingRecordId = null;

    public function mount($employeeId)
    {
        $this->employeeId = $employeeId;
        $this->employee = User::findOrFail($employeeId);
        $this->mcu_year = date('Y');
        $this->mcu_date = date('Y-m-d');
    }

    public function openAddModal()
    {
        $this->reset(['editingRecordId', 'mcu_year', 'mcu_date', 'attendance_status', 'process_status', 'medical_status', 'doctor_notes']);
        $this->mcu_year = date('Y');
        $this->mcu_date = date('Y-m-d');
        $this->showAddModal = true;
    }

    public function editMcuRecord($recordId)
    {
        $record = McuRecord::with('result')->findOrFail($recordId);
        $this->editingRecordId = $record->id;
        $this->mcu_year = $record->mcu_year;
        $this->mcu_date = $record->mcu_date;
        $this->attendance_status = $record->attendance_status;
        $this->process_status = $record->process_status;
        $this->medical_status = $record->result ? $record->result->status : 'fit_to_work';
        $this->doctor_notes = $record->result ? $record->result->doctor_notes : '';
        $this->showAddModal = true;
    }

    public function updatedAttendanceStatus($value)
    {
        if ($value === 'absent') {
            $this->process_status = 'rescheduled';
            $this->medical_status = 'not_examined';
        } elseif ($value === 'scheduled') {
            $this->process_status = 'scheduled';
            $this->medical_status = 'not_examined';
        }
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->editingRecordId = null;
    }

    public function deleteMcuRecord($recordId)
    {
        $record = McuRecord::findOrFail($recordId);
        $record->delete();

        $this->dispatch('alert', [
            'text' => 'Riwayat MCU berhasil dihapus.',
            'duration' => 3000,
            'close' => true,
            'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
        ]);
    }

    public function saveMcuRecord()
    {
        $this->validate([
            'mcu_year' => 'required|numeric',
            'mcu_date' => 'required|date',
            'attendance_status' => 'required|string',
            'process_status' => 'required|string',
            'medical_status' => 'required|string',
            'doctor_notes' => 'nullable|string',
        ]);

        $notesToSave = $this->medical_status === 'fit_with_notes' ? $this->doctor_notes : null;

        DB::beginTransaction();
        try {
            if ($this->editingRecordId) {
                // Edit mode
                $record = McuRecord::findOrFail($this->editingRecordId);
                $record->update([
                    'mcu_year' => $this->mcu_year,
                    'mcu_date' => $this->mcu_date,
                    'attendance_status' => $this->attendance_status,
                    'process_status' => $this->process_status,
                ]);

                if ($record->result) {
                    $record->result->update([
                        'status' => $this->medical_status,
                        'doctor_notes' => $notesToSave,
                    ]);
                } else {
                    $record->result()->create([
                        'status' => $this->medical_status,
                        'doctor_notes' => $notesToSave,
                        'workflow_status' => 'reviewed',
                    ]);
                }

                $msg = 'Riwayat MCU berhasil diperbarui.';
            } else {
                // Create mode
                $record = McuRecord::create([
                    'employee_id' => $this->employeeId,
                    'mcu_year' => $this->mcu_year,
                    'mcu_date' => $this->mcu_date,
                    'attendance_status' => $this->attendance_status,
                    'process_status' => $this->process_status,
                    'notification_status' => 'notified',
                ]);

                // Create result skeleton
                $record->result()->create([
                    'status' => $this->medical_status,
                    'doctor_notes' => $notesToSave,
                    'workflow_status' => 'reviewed',
                ]);

                $msg = 'Riwayat MCU berhasil ditambahkan.';
            }

            // --- Logika Otomatis Reschedule ---
            if ($record->process_status === 'rescheduled' && null === $record->rescheduled_to_id) {
                $newDate = \Carbon\Carbon::parse($record->mcu_date)->addMonths(3);
                
                $newRecord = McuRecord::create([
                    'employee_id' => $record->employee_id,
                    'mcu_year' => $newDate->year,
                    'mcu_date' => $newDate->toDateString(),
                    'attendance_status' => 'scheduled',
                    'process_status' => 'scheduled',
                    'notification_status' => 'pending',
                    'reschedule_count' => ($record->reschedule_count ?? 0) + 1,
                ]);

                $newRecord->result()->create([
                    'status' => 'not_examined',
                    'workflow_status' => 'reviewed',
                ]);

                $record->update(['rescheduled_to_id' => $newRecord->id]);

                $userToNotify = User::with('contractors')->find($record->employee_id);
                if ($userToNotify) {
                    $userToNotify->notify(new \App\Notifications\McuRescheduledNotification($newRecord));
                }
                
                $msg .= ' (Jadwal Reschedule berhasil dibuat untuk ' . $newDate->translatedFormat('d F Y') . ')';
            }
            // ----------------------------------

            DB::commit();

            $this->closeAddModal();
            $this->dispatch('alert', [
                'text' => $msg,
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
