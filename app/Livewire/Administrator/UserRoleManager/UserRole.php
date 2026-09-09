<?php

namespace App\Livewire\Administrator\UserRoleManager;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserRole extends Component
{
    use WithPagination;
    public $roles;
    public $selectedUser;
    public $selectedRoles = [];
    public $showModal = false;
    public $search = '';
    public $roleFilter = '';
    public function mount()
    {
        $this->roles = Role::all();
    }
    public function updatingPage()
    {
        $this->reset('showModal'); // modal tertutup ketika pindah halaman
    }
    public function updatingSearch()
    {
        $this->resetPage(); // reset ke page 1 saat search berubah
    }
    public function openModal($userId)
    {
        $this->selectedUser = User::with('roles')->findOrFail($userId);
        $this->selectedRoles = $this->selectedUser->roles->pluck('id')->toArray();
        $this->showModal = true;
    }

    public function save()
    {
        $this->selectedUser->roles()->sync($this->selectedRoles);
        $this->showModal = false;

        session()->flash('message', 'Role berhasil diperbarui.');
    }
    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->when(
                $this->search,
                fn($q) =>
                $q->where(
                    fn($sub) =>
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                )
            )
            ->when(
                $this->roleFilter,
                fn($q) =>
                $q->whereHas(
                    'roles',
                    fn($sub) =>
                    $sub->where('name', $this->roleFilter)
                )
            )
            ->paginate(10);
        return view('livewire.administrator.user-role-manager.user-role', [
            'users' => $users
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
