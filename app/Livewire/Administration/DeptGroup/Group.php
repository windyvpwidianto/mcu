<?php

namespace App\Livewire\Administration\DeptGroup;

use App\Models\Group as ModelsGroup;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Livewire\WithoutUrlPagination;
use Illuminate\Support\Facades\Route;
class Group extends Component
{
    use WithPagination, WithoutUrlPagination;
    public $legend;
    #[Validate('required', message: 'kolom nama perusahaan tidak boleh kosong!!!')]
    public $group_name;
    public $showConfirmModal = false;
    public $search_group, $group_id, $delete_id;
    public $activeTab;

    public function mount()
    {
        $currentRoute = Route::currentRouteName();

        if ($currentRoute === 'administration-department-group') {
            $this->activeTab = 'department';
        } elseif ($currentRoute === 'administration-department-group-group') {
            $this->activeTab = 'group';
        } else {
            // Default jika rute tidak cocok (misalnya rute induk)
            $this->activeTab = 'department';
        }
    }
    public function open_modal()
    {
        Flux::modal('group')->show();
        $this->legend = 'Input group';
    }
    public function open_modal_edit(ModelsGroup $id)
    {
        Flux::modal('group_edit')->show();
        if ($id->id) {
            $this->group_id = $id->id;
            $this->group_name = $id->group_name;
            $this->legend = 'Edit group';
        } else {
            $this->reset('group_id', 'group_name');
        }
    }
    public function close_modal()
    {
        Flux::modal('group_edit')->close();
        Flux::modal('group')->close();
        $this->reset('group_id', 'group_name');
    }
    public function store()
    {
        $this->validate();
        ModelsGroup::updateOrCreate(['id' => $this->group_id], [
            'group_name' => $this->group_name
        ]);
        if ($this->group_id) {
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
        $this->dispatch('group-created');
        $this->reset('group_id', 'group_name',);
    }
    public function showDelete(ModelsGroup $id)
    {
        Flux::modal('delete-group')->show();
        $this->delete_id = $id->id;
        $this->group_name = $id->group_name;
    }
    public function delete()
    {
        $deleteFile = ModelsGroup::whereId($this->delete_id);
        $deleteFile->delete();
        Flux::modal('delete-group')->close();
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
        return view('livewire.administration.dept-group.group', [
            'Groups' => ModelsGroup::search(trim($this->search_group))->paginate(30)
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
