<?php

namespace App\Livewire\Administration\Companies;

use App\Imports\CompanyImport;
use Flux\Flux;
use App\Models\Company;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Maatwebsite\Excel\Facades\Excel;

class CompanyIndex extends Component
{
    use WithPagination,WithFileUploads;
    public $legend;
    #[Validate('required', message: 'kolom nama perusahaan tidak boleh kosong!!!')]
    public $company_name;
    #[Validate('required', message: 'kolom nama status tidak boleh kosong!!!')]
    public $status;
    public $upload_data;
    public $showConfirmModal = false;
    public $search_company, $company_id,$delete_id;

    public function open_modal(Company $id)
    {
        Flux::modal('company')->show();
        $this->company_id = $id->id;
        if ($id->id) {
            $this->company_name = $id->company_name;
            $this->status = $id->status;
            $this->legend = 'Edit Company';
        } else {
            $this->legend = 'Input Company';
              $this->reset('company_id', 'company_name','status');
        }
    }
    public function open_modal_opload(){
        Flux::modal('upload')->show();
    }
    public function close_modal_upload(){
        Flux::modal('upload')->close();
    }
    public function import()
    {
         $this->validate(
            [
                'upload_data' => 'required',
                'upload_data' => 'mimes:csv,xlsx',
            ],
            [
                'upload_data' => 'kolom ini tidak boleh kosong!!!',
            ]);
        Excel::import(new CompanyImport, $this->upload_data);
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
        $this->reset('company_id', 'company_name','status');
        Flux::modal('company')->close();
    }
    public function store()
    {
        $this->validate();
        Company::updateOrCreate(['id' => $this->company_id], [
            'company_name' => $this->company_name,
            'status' => $this->status
        ]);
        if ($this->company_id) {
            $text = 'Data berhasil di edit!!!';
        } else {
            $text = 'Data berhasil di input!!!';
             $this->reset('company_id', 'company_name','status');
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
    public function showDelete(Company $id)
    {
       Flux::modal('delete-company')->show();
        $this->delete_id = $id->id;
        $this->company_name = $id->company_name;
    }
    public function delete()
    {
        $deleteFile = Company::whereId($this->delete_id);
        $deleteFile->delete();
       Flux::modal('delete-company')->close();
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
        $title = "Home";
        return view('livewire.administration.companies.company-index',  [
            'title' => $title,
            'Companies' => Company::search(trim($this->search_company))->paginate(20)
        ]);
    }
    public function paginationView()
    {
       return 'paginate.pagination';
    }
}
