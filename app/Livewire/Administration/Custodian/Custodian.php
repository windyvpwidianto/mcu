<?php

namespace App\Livewire\Administration\Custodian;

use Flux\Flux;
use App\Models\Company;
use App\Models\Contractor;
use Livewire\Component;
use App\Models\Department;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Livewire\WithoutUrlPagination;
use App\Models\Custodian as ModelsCustodian;

class Custodian extends Component
{
    use WithPagination, WithoutUrlPagination;
    public $legend;
    #[Validate('required', message: 'kolom ini tidak boleh kosong!!!')]
    public $contractor_id, $department_id, $status;
    public $search_department, $custodian_id, $contractor_enabled, $contractor_name = 'semua perusahaan';
    public $selectedDeptId;
    public $selectedContractorId;
    public function resetFilds()
    {
        $this->reset('contractor_id', 'department_id', 'status', 'custodian_id');
    }
    public function open_modal()
    {
        Flux::modal('custodian')->show();
        $this->legend = 'Input Custodian';
    }
    public function modalEdit(Department $dept, Contractor $comp)
    {

        Flux::modal('custodian-edit')->show();
        $id = ModelsCustodian::where('department_id', $dept->id)->where('contractor_id', $comp->id)->first();
        $this->custodian_id = $id->id;
        $this->contractor_id = $id->contractor_id;
        $this->department_id = $id->department_id;
        $this->status = $id->status;
        $this->legend = 'Edit Custodian';
    }
    public function close_modal()
    {
        if ($this->custodian_id) {
            $this->resetFilds();
            Flux::modal('custodian-edit')->close();
        } else {
            $this->resetFilds();
            Flux::modal('custodian')->close();
        }
    }
    public function store()
    {
        $this->validate();
        ModelsCustodian::updateOrCreate(['id' => $this->custodian_id], [
            'contractor_id' => $this->contractor_id,
            'department_id' => $this->department_id,
            'status' => $this->status
        ]);
        if ($this->custodian_id) {
            $text = 'Data berhasil di edit!!!';
        } else {
            $text = 'Data berhasil di input!!!';
        }
        $this->dispatch(
            'alert',
            [
                'text' => $text,
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]
        );
        $this->resetFilds();
    }
    public function confirmDelete($deptId, $contractorId)
    {
        // Simpan ID ke dalam property agar bisa diakses saat tombol "Delete" di klik
        $this->selectedDeptId = $deptId;
        $this->selectedContractorId = $contractorId;

        Flux::modal('delete-custodian')->show();
    }

    public function delete()
    {
        // Cari data berdasarkan property yang sudah di-set tadi
        $dept = \App\Models\Department::find($this->selectedDeptId);

        if ($dept) {
            $dept->contractors()->detach($this->selectedContractorId);
        }

        $this->resetFilds(); // Pastikan typo 'filds' sesuai dengan nama fungsi Anda

        $this->dispatch('alert', [
            'text' => "Data berhasil di hapus!!!",
            'duration' => 5000,
            'backgroundColor' => "linear-gradient(to right, #ff3333, #ff6666)",
        ]);

        Flux::modal('delete-custodian')->close();
    }
    public function id_contractor(Contractor $id)
    {
        if (!empty($id->id)) {
            $this->contractor_enabled = $id->id;
            $this->contractor_name = $id->contractor_name;
        } else {
            $this->reset('contractor_enabled');
            $this->contractor_name = 'semua perusahaan';
        }
    }
    public function render()
    {
        return view('livewire.administration.custodian.custodian', [
            'Contractors' => Contractor::get(),
            'Department' => Department::get(),
            'Departments' => Department::with('contractors')->searchdept(trim($this->search_department))->paginate(20)
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
