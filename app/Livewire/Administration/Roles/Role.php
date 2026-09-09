<?php

namespace App\Livewire\Administration\Roles;

use App\Models\Role as Roles;
use Livewire\Component;
use Livewire\WithPagination;

class Role extends Component
{
    use WithPagination;

    public $name;
    public $roleId;
    public $modalOpen = false;
    public $isEditing = false;

    protected $rules = [
        'name' => 'required|string|unique:roles,name',
    ];
    public function resetForm()
    {
        $this->reset(['name', 'roleId', 'isEditing']);
        $this->resetValidation();
    }

    public function openModal($isEditing = false, $id = null)
    {
        $this->resetValidation();
        $this->isEditing = $isEditing;
        $this->modalOpen = true;

        if ($isEditing && $id) {
            $role = Roles::findOrFail($id);
            $this->roleId = $role->id;
            $this->name = $role->name;
        } else {
            $this->reset(['name', 'roleId']);
        }
    }

    public function closeModal()
    {
        $this->reset(['modalOpen', 'name', 'roleId', 'isEditing']);
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();

        Roles::create(['name' => $this->name]);

        $this->closeModal();
        $this->dispatch(
            'alert',
            [
                'text' => "Role berhasil ditambahkan!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]
        );
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|unique:roles,name,' . $this->roleId,
        ]);

        $role = Roles::findOrFail($this->roleId);
        $role->update(['name' => $this->name]);

        $this->closeModal();
         $this->dispatch(
            'alert',
            [
                'text' => "Role berhasil diperbarui!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]
        );
    }

    public function delete($id)
    {
        Roles::findOrFail($id)->delete();
         $this->dispatch(
            'alert',
            [
                'text' => "Role berhasil dihapus!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #f44336, #d32f2f);",
            ]
        );
    }
    public function render()
    {
        return view('livewire.administration.roles.role', [
            'roles' => Roles::latest()->paginate(10),
        ]);
    }
}
