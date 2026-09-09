<?php
namespace App\Livewire\Administration\EventGeneral;

use App\Imports\EventSubTypeImport;
use App\Models\EventSubType as ET;
use App\Models\EventType;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class EventSubType extends Component
{
    use WithPagination, WithoutUrlPagination, WithFileUploads;
    public $legend;
    #[Validate('required', message: 'kolom ini tidak boleh kosong!!!')]
    public $event_sub_type_name, $status, $event_type_id;
    public $upload_data;
    public $showConfirmModal = false;
    public $search_event_sub_type, $event_sub_type_id, $delete_id, $search_event_type;

    public function open_modal()
    {
        Flux::modal('EventSubType')->show();
        $this->legend = 'Tambah Event Type';
    }
    public function open_modal_edit(ET $id)
    {
        $this->event_sub_type_id = $id->id;
        Flux::modal('EventSubTypeEdit')->show();
        if ($id->id) {
            $this->event_sub_type_name = $id->event_sub_type_name;
            $this->event_type_id       = $id->event_type_id;
            $this->status              = $id->status;
            $this->legend              = 'Edit Event Type';
        }
    }
    public function close_modal()
    {
        if ($this->event_sub_type_id) {
            Flux::modal('EventSubTypeEdit')->close();
        } else {
            Flux::modal('EventSubType')->close();
        }
        $this->reset('event_type_id', 'event_sub_type_name', 'status');
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
        Excel::import(new EventSubTypeImport, $this->upload_data);
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
        ET::updateOrCreate(['id' => $this->event_sub_type_id], [
            'event_sub_type_name' => $this->event_sub_type_name,
            'event_type_id'       => $this->event_type_id,
            'status'              => $this->status,
        ]);
        if ($this->event_sub_type_id) {
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
        $this->reset('event_type_id', 'event_sub_type_name', 'status');
    }
    public function showDelete(ET $id)
    {
        Flux::modal('delete-event-type')->show();
        $this->delete_id           = $id->id;
        $this->event_sub_type_name = $id->event_sub_type_name;
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
        return view('livewire.administration.event-general.event-sub-type', [
            'EventSubType' => ET::with('EventType')->search(trim($this->search_event_type))->searchEventType(trim($this->search_event_type))->paginate(20),
            'EventType'    => EventType::get(),
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
