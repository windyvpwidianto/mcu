<?php

namespace App\Livewire\Administration\BusinessUnit;

use Flux\Flux;
use Livewire\Component;
use App\Models\BusinessUnit;
use App\Models\Company;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Livewire\WithoutUrlPagination;

class BusinessUnits extends Component
{
    use WithPagination, WithoutUrlPagination;
    public $legend;
    #[Validate('required', message: 'kolom nama perusahaan tidak boleh kosong!!!')]
    public $company_name, $status;
    public $showConfirmModal = false;
    public $search_company, $bu_id, $delete_id, $company_id, $bu_name;
    public function resetFilds()
    {
        $this->reset('company_id', 'company_name', 'status', 'bu_id');
    }
    public function open_modal(BusinessUnit $id)
    {
        Flux::modal('bu')->show();
        if ($id->id) {
            $this->bu_id = $id->id;
            $this->company_name = $id->company_name;
            $this->company_id = $id->company_id;
            $this->status = $id->status;
            $this->legend = 'Edit Company';
        } else {
            $this->legend = 'Input Company';
            $this->resetFilds();
        }
    }
    public function close_modal()
    {
        $this->resetFilds();
        Flux::modal('bu')->close();
    }
    public function store()
    {
        $this->validate();
        BusinessUnit::updateOrCreate(['id' => $this->bu_id], [
            'company_name' => $this->company_name,
            'company_id' => $this->company_id,
            'status' => $this->status
        ]);
        if ($this->bu_id) {
            $text = 'Data berhasil di edit!!!';
        } else {
            $text = 'Data berhasil di input!!!';
            $this->resetFilds();
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
        $this->reset('company_id', 'company_name', 'status');
    }
    public function showDelete(BusinessUnit $id)
    {
        Flux::modal('delete-bu')->show();
        $this->delete_id = $id->id;
        $this->bu_name = $id->company_name;
    }
    public function delete()
    {
        $deleteFile = BusinessUnit::whereId($this->delete_id);
        $deleteFile->delete();
        Flux::modal('delete-bu')->close();
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
        return view('livewire.administration.business-unit.business-units', [
            'business_unit' => BusinessUnit::with('companies')->paginate(20),
            'Companies' => Company::where('status', 'enabled')->get()
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
