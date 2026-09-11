<?php

namespace App\Livewire\Mcu;

use App\Models\McuMasterData;
use Livewire\Component;
use Livewire\WithPagination;

class GenerateSchedule extends Component 
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $isEdit = false;
    public $masterDataId;

    // Form fields
    public $employee_name;
    public $nik;
    public $company;
    public $position;
    public $birth_date;
    public $ktp_number;
    public $hp_number;
    public $mcu_date;

    // History fields
    public $showHistoryModal = false;
    public $historyMasterId = null;
    public $employeeHistoryName = '';
    public $histories = [];
    public $new_history_date;
    public $new_history_notes;

    public function rules()
    {
        return [
            'employee_name' => 'required|string|max:255',
            'nik' => 'required|string|max:50',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'ktp_number' => 'nullable|string|max:50',
            'hp_number' => 'nullable|string|max:20',
            'mcu_date' => 'required|date',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->reset([
            'employee_name', 'nik', 'company', 'position',
            'birth_date', 'ktp_number', 'hp_number', 'mcu_date',
            'isEdit', 'masterDataId'
        ]);
    }

    public function save()
    {
        $validatedData = $this->validate();
        
        // Reset notification status pending when modifying MCU Date
        $validatedData['notification_status'] = 'pending';

        if ($this->isEdit) {
            $record = McuMasterData::find($this->masterDataId);
            $record->update($validatedData);
            $message = 'Data MCU Master berhasil diperbarui!';
        } else {
            McuMasterData::create($validatedData);
            $message = 'Data MCU Master berhasil ditambahkan!';
        }

        $this->closeModal();
        $this->dispatch('alert', [
            'text'            => $message,
            'duration'        => 3000,
            'close'           => true,
            'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
        ]);
    }

    public function edit($id)
    {
        $record = McuMasterData::findOrFail($id);
        $this->masterDataId = $record->id;
        $this->employee_name = $record->employee_name;
        $this->nik = $record->nik;
        $this->company = $record->company;
        $this->position = $record->position;
        $this->birth_date = $record->birth_date ? $record->birth_date->format('Y-m-d') : null;
        $this->ktp_number = $record->ktp_number;
        $this->hp_number = $record->hp_number;
        $this->mcu_date = $record->mcu_date ? $record->mcu_date->format('Y-m-d') : null;

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        McuMasterData::findOrFail($id)->delete();
        $this->dispatch('alert', [
            'text'            => 'Data berhasil dihapus.',
            'duration'        => 3000,
            'close'           => true,
            'backgroundColor' => "linear-gradient(to right, #ef4444, #f87171)",
        ]);
    }

    public function openHistory($masterId)
    {
        $this->historyMasterId = $masterId;
        $master = McuMasterData::with('histories')->find($masterId);
        if ($master) {
            $this->employeeHistoryName = $master->employee_name;
            $this->histories = $master->histories;
            $this->showHistoryModal = true;
        }
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->reset(['historyMasterId', 'employeeHistoryName', 'histories', 'new_history_date', 'new_history_notes']);
    }

    public function addHistory()
    {
        $this->validate([
            'new_history_date' => 'required|date',
            'new_history_notes' => 'nullable|string|max:255'
        ]);

        $master = McuMasterData::find($this->historyMasterId);
        if ($master) {
            $master->histories()->create([
                'historical_date' => $this->new_history_date,
                'notes' => $this->new_history_notes
            ]);
            
            $this->histories = $master->histories()->get();
            $this->reset(['new_history_date', 'new_history_notes']);
            
            $this->dispatch('alert', [
                'text' => 'Riwayat berhasil ditambahkan!',
                'duration' => 3000,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]);
        }
    }
    
    public function deleteHistory($historyId)
    {
        \App\Models\McuHistory::find($historyId)?->delete();
        $master = McuMasterData::find($this->historyMasterId);
        if ($master) {
            $this->histories = $master->histories()->get();
        }
    }

    public function paginationView()
    {
        return 'paginate.pagination';
    }

    public function render()
    {
        $data = McuMasterData::where('employee_name', 'like', '%' . $this->search . '%')
            ->orWhere('nik', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.mcu.generate-schedule', [
            'masterData' => $data
        ]);
    }
}
