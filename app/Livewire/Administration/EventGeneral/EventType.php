<?php

namespace App\Livewire\Administration\EventGeneral;

use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;
use App\Models\EventCategory;
use Livewire\WithFileUploads;
use App\Models\EventType as ET;
use App\Imports\EventTypeImport;
use Livewire\Attributes\Validate;
use Livewire\WithoutUrlPagination;

class EventType extends Component
{
    use WithPagination, WithoutUrlPagination, WithFileUploads;
    public $legend;
    #[Validate('required', message: 'kolom ini tidak boleh kosong!!!')]
    public $event_type_name, $status,$event_category_id;
    public $upload_data;
    public $showConfirmModal = false;
    public $search_event_type, $event_type_id, $delete_id,$search_event_category;

    public function open_modal()
    {
        Flux::modal('EventType')->show();
        $this->legend = 'Tambah Event Type';
    }
    public function open_modal_edit(ET $id)
    {
        $this->event_type_id = $id->id;
        Flux::modal('EventTypeEdit')->show();
        if ($id->id) {
            $this->event_type_name = $id->event_type_name;
            $this->event_category_id              = $id->event_category_id;
            $this->status              = $id->status;
            $this->legend              = 'Edit Event Type';
        }
    }
    public function close_modal()
    {
        if ($this->event_type_id) {
            Flux::modal('EventTypeEdit')->close();
        } else {
            Flux::modal('EventType')->close();
        }
        $this->reset('event_category_id', 'event_type_name', 'status');
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
        Excel::import(new EventTypeImport, $this->upload_data);
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
        ET::updateOrCreate(['id' => $this->event_type_id], [
            'event_type_name' => $this->event_type_name,
            'event_category_id' => $this->event_category_id,
            'status'              => $this->status,
        ]);
        if ($this->event_type_id) {
            $text = 'Data berhasil di edit!!!';
        } else {
            $text = 'Data berhasil di input!!!';
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
        $this->reset('event_category_id', 'event_type_name', 'status');
    }
    public function showDelete(ET $id)
    {
        Flux::modal('delete-event-type')->show();
        $this->delete_id           = $id->id;
        $this->event_type_name = $id->event_type_name;
    }
    public function delete()
    {
        $deleteFile = ET::whereId($this->delete_id);
        $deleteFile->delete();
        Flux::modal('delete-event-type')->close();
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
        return view('livewire.administration.event-general.event-type',[
            'EventType' => ET::with('EventCategories')->search(trim($this->search_event_type))->searchEventCategory(trim($this->search_event_category))->paginate(20),
            'EventCategory'=>EventCategory::get()
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
