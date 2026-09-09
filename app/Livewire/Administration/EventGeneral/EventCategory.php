<?php
namespace App\Livewire\Administration\EventGeneral;

use App\Imports\EventCategoryImport;
use App\Models\EventCategory as EC;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class EventCategory extends Component
{
    use WithPagination, WithoutUrlPagination, WithFileUploads;
    public $legend;
    #[Validate('required', message: 'kolom ini tidak boleh kosong!!!')]
    public $event_category_name, $status;
    public $upload_data;
    public $showConfirmModal = false;
    public $search_event_category, $event_category_id, $delete_id;

    public function open_modal()
    {
        Flux::modal('EventCategory')->show();
        $this->legend = 'Tambah Event Kategory';
    }
    public function open_modal_edit(EC $id)
    {
        $this->event_category_id = $id->id;
        Flux::modal('EventCategoryEdit')->show();
        if ($id->id) {
            $this->event_category_name = $id->event_category_name;
            $this->status              = $id->status;
            $this->legend              = 'Edit Event Kategory';
        }
    }
    public function close_modal()
    {
        if ($this->event_category_id) {
            Flux::modal('EventCategoryEdit')->close();
        } else {
            Flux::modal('EventCategory')->close();
        }
        $this->reset('event_category_id', 'event_category_name', 'status');
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
                'upload_data' => 'required',
                'upload_data' => 'mimes:csv,xlsx',
            ],
            [
                'upload_data' => 'kolom ini tidak boleh kosong!!!',
            ]);
        Excel::import(new EventCategoryImport, $this->upload_data);
        $this->dispatch(
            'alert',
            [
                'text'            => 'upload data sukses!!!',
                'duration'        => 5000,
                'destination'     => '/contact',
                'newWindow'       => true,
                'close'           => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]
        );
        $this->reset('upload_data');
    }

    public function store()
    {
        $this->validate();
        EC::updateOrCreate(['id' => $this->event_category_id], [
            'event_category_name' => $this->event_category_name,
            'status'              => $this->status,
        ]);
        if ($this->event_category_id) {
            $text = 'Data berhasil di edit!!!';
        } else {
            $text = 'Data berhasil di input!!!';
             $this->reset('event_category_id', 'event_category_name', 'status');
        }

        $this->dispatch(
            'alert',
            [
                'text'            => $text,
                'duration'        => 5000,
                'destination'     => '/contact',
                'newWindow'       => true,
                'close'           => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]
        );

    }
    public function showDelete(EC $id)
    {
        Flux::modal('delete-company')->show();
        $this->delete_id           = $id->id;
        $this->event_category_name = $id->event_category_name;
    }
    public function delete()
    {
        $deleteFile = EC::whereId($this->delete_id);
        $deleteFile->delete();
        Flux::modal('delete-company')->close();
        $this->dispatch(
            'alert',
            [
                'text'            => "Data berhasil di hapus!!!",
                'duration'        => 5000,
                'destination'     => '/contact',
                'newWindow'       => true,
                'close'           => true,
                'backgroundColor' => "linear-gradient(to right, #ff3333, #ff6666)",
            ]
        );
    }
    public function render()
    {
        return view('livewire.administration.event-general.event-category', [
            'EventCategory' => EC::search(trim($this->search_event_category))->paginate(20),
        ]);
    }
    public function paginationView()
    {
       return 'paginate.pagination';
    }
}
