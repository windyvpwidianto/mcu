<?php

namespace App\Livewire\Administration\Contractor;

use Flux\Flux;
use Livewire\Component;
use App\Models\Contractor;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Livewire\WithoutUrlPagination;

class Contractors extends Component
{
    use WithPagination, WithoutUrlPagination;
    public $legend;
    #[Validate('required', message: 'kolom nama perusahaan tidak boleh kosong!!!')]
    public $contractor_name,$status;
    public $showConfirmModal = false;
    public $search_contractor, $contractor_id,$delete_id;

    public function open_modal(Contractor $id)
    {
        Flux::modal('contractor')->show();
        if ($id->id) {
            $this->contractor_id = $id->id;
            $this->contractor_name = $id->contractor_name;
            $this->status = $id->status;
            $this->legend = 'Edit contractor';
        } else {
            $this->legend = 'Input contractor';
              $this->reset('contractor_id', 'contractor_name','status');
        }
    }
    public function close_modal()
    {
        $this->reset('contractor_id', 'contractor_name','status');
        Flux::modal('contractor')->close();
    }
    public function store()
    {
        $this->validate();
        Contractor::updateOrCreate(['id' => $this->contractor_id], [
            'contractor_name' => $this->contractor_name,
            'status' => $this->status
        ]);
        if ($this->contractor_id) {
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
           $this->reset('contractor_id', 'contractor_name','status');
    }
    public function showDelete(Contractor $id)
    {
       Flux::modal('delete-contractor')->show();
        $this->delete_id = $id->id;
        $this->contractor_name = $id->contractor_name;
    }
    public function delete()
    {
        $deleteFile = Contractor::whereId($this->delete_id);
        $deleteFile->delete();
       Flux::modal('delete-contractor')->close();
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
        return view('livewire.administration.contractor.contractors',[
            'Contractors' => Contractor::search(trim($this->search_contractor))->paginate(20)
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
