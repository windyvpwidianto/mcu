<?php

namespace App\Livewire\Administration\Departement;

use Flux\Flux;
use App\Models\Company;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Imports\DepartmentImport;
use Livewire\Attributes\Validate;
use Livewire\WithoutUrlPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Department as ModelDepartment;

class Department extends Component
{
    use WithPagination, WithoutUrlPagination, WithFileUploads;
    public $legend;
    #[Validate('required', message: 'kolom tidak boleh kosong!!!')]
    public $department_name, $status;
    public $upload_data;
    public $dept_id, $search_department, $search_company, $search_company_id, $company_name;
    public function resetFilds()
    {
        $this->reset('department_name', 'status', 'dept_id');
    }
    public function open_modal(ModelDepartment $id)
    {
        Flux::modal('dept')->show();
        if ($id->id) {
            $this->dept_id = $id->id;
            $this->department_name = $id->department_name;
            $this->status = $id->status;
            $this->legend = 'Edit Department';
        } else {
            $this->legend = 'Input Department';
            $this->resetFilds();
        }
    }
    public function open_modal_opload()
    {
        Flux::modal('upload')->show();
    }
    public function close_modal_upload()
    {
        Flux::modal('upload')->close();
    }
    public function import()
    {
        $this->validate(
            [
                'upload_data' => 'required|mimes:csv,xlsx'
            ],
            [
                'upload_data' => 'kolom tidak boleh kosong!!!',
                'upload_data.mimes' => 'Only csv and xlsx file types are allowed!!!'
            ]
        );
        Excel::import(new DepartmentImport, $this->upload_data);
        $this->dispatch(
            'alert',
            [
                'text' => 'upload data sukses!!!',
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]
        );
        $this->reset('upload_data');
    }
    public function close_modal()
    {
        $this->resetFilds();
        Flux::modal('dept')->close();
    }
    public function store()
    {
        $this->validate();
        ModelDepartment::updateOrCreate(['id' => $this->dept_id], [
            'department_name' => $this->department_name,
            'status' => $this->status

        ]);
        if ($this->dept_id) {
            $text = 'Data berhasil di edit!!!';
        } else {
            $this->resetFilds();
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
    }
    public function delete($id)
    {
        $deleteFile = ModelDepartment::whereId($id);
        $deleteFile->delete();
        $this->dispatch(
            'alert',
            [
                'text' => "Data berhasil di hapus!!!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #ff3333, #ff6666)",
            ]
        );
    }


    public function render()
    {
        if ($this->search_company_id) {
            $Departments = ModelDepartment::searchdept(trim($this->search_department))->searchCompany(trim($this->company_name))->paginate(20);
        } else {
            $this->company_name = "Semua Perusahaan";
            $Departments = ModelDepartment::searchdept(trim($this->search_department))->paginate(20);
        }
        return view('livewire.administration.departement.department', [
            'Companies' => Company::get(),
            'Departments' => $Departments
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
