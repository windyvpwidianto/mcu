<?php

namespace App\Livewire\Administration\DeptGroup;

use Flux\Flux;
use App\Models\Group;
use Livewire\Component;
use App\Models\Department;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Models\Department_group;
use Livewire\Attributes\Validate;
use Livewire\WithoutUrlPagination;
use Illuminate\Support\Facades\Route;
class DepartmentGroup extends Component
{
    use WithPagination, WithoutUrlPagination;
    public $legend;
    #[Validate('required', message: 'kolom ini tidak boleh kosong!!!')]
    public $group_id, $department_id, $status;
    public $search, $dept_group_id;

    // Properti yang menentukan tab mana yang harus ditampilkan
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
    public function resetFilds()
    {
        $this->reset('group_id', 'department_id', 'status', 'dept_group_id');
    }
    public function open_modal()
    {
        Flux::modal('dept-group')->show();
        $this->legend = 'Input Custodian';
    }
    public function modalEdit(Group $group, Department $dept)
    {
        Flux::modal('dept-group-edit')->show();
        $id = Department_group::where('group_id', $group->id)->where('department_id', $dept->id)->first();
        $this->dept_group_id = $id->id;
        $this->group_id = $id->group_id;
        $this->department_id = $id->department_id;
        $this->status = $id->status;
        $this->legend = 'Edit Custodian';
    }
    public function close_modal()
    {
        if ($this->dept_group_id) {
            $this->resetFilds();
            Flux::modal('dept-group-edit')->close();
        } else {
            $this->resetFilds();
            Flux::modal('dept-group')->close();
        }
    }
    public function store()
    {
        $this->validate();
        Department_group::updateOrCreate(['id' => $this->dept_group_id], [
            'group_id' => $this->group_id,
            'department_id' => $this->department_id,
            'status' => $this->status
        ]);
        if ($this->dept_group_id) {
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
         $this->reset( 'department_id',  'dept_group_id');
    }
    public function confirmDelete()
    {
        Flux::modal('delete-dept-group')->show();
    }
    public function delete(Group $group, Department $dept)
    {
        $group->departments()->detach($dept->id);
        $this->resetFilds();
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
        Flux::modal('delete-dept-group')->close();
    }
    #[On('group-created')]
    public function render()
    {
        return view('livewire.administration.dept-group.department-group', [
            'Groups' => Group::search(trim($this->search))->paginate(30),
            'Department' => Department::where('status', 'enabled')->get()
        ]);
    }
}
