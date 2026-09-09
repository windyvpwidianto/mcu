<?php

namespace App\Livewire\Administration\Locations;

use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;
use App\Imports\CompanyImport;
use App\Imports\LocationsImport;
use Livewire\Attributes\Validate;
use Livewire\WithoutUrlPagination;
use App\Models\Location as ModelsLocation;

class Location extends Component
{
    use WithPagination, WithoutUrlPagination,WithFileUploads;
    public $legend;
    #[Validate('required', message: 'kolom nama perusahaan tidak boleh kosong!!!')]
    public $lokasi_name;
    #[Validate('required', message: 'kolom nama status tidak boleh kosong!!!')]
    public $status;
    public $upload_data;
    public $showConfirmModal = false;
    public $search_lokasi, $lokasi_id,$delete_id;
    public function open_modal(ModelsLocation $id)
    {
        Flux::modal('lokasi')->show();
        $this->lokasi_id = $id->id;
        if ($id->id) {
            $this->lokasi_name = $id->name;
            $this->status = $id->status;
            $this->legend = 'Edit Company';
        } else {
            $this->legend = 'Input Company';
              $this->reset('lokasi_id', 'lokasi_name','status');
        }
    }
    public function open_modal_opload(){
        Flux::modal('upload')->show();
    }
    public function close_modal_upload(){
        Flux::modal('upload')->close();
    }
        public function close_modal()
    {
        $this->reset('lokasi_id', 'lokasi_name','status');
        Flux::modal('lokasi')->close();
    }
    public function store()
    {
        $this->validate();
        ModelsLocation::updateOrCreate(['id' => $this->lokasi_id], [
            'name' => $this->lokasi_name,
            'status' => $this->status
        ]);
        if ($this->lokasi_id) {
            $text = 'Data berhasil di edit!!!';
        } else {
            $text = 'Data berhasil di input!!!';
             $this->reset('lokasi_id', 'lokasi_name','status');
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
        Excel::import(new LocationsImport, $this->upload_data);
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
    public function showDelete(ModelsLocation $id)
    {
       Flux::modal('delete-lokasi')->show();
        $this->delete_id = $id->id;
        $this->lokasi_name = $id->name;
    }
    public function delete()
    {
        $deleteFile = ModelsLocation::whereId($this->delete_id);
        $deleteFile->delete();
       Flux::modal('delete-lokasi')->close();
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
        return view('livewire.administration.locations.location',[
            'location'=>ModelsLocation::search(trim($this->search_lokasi))->paginate(20)
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
