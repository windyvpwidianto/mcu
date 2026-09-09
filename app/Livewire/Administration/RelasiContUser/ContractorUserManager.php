<?php

namespace App\Livewire\Administration\RelasiContUser;

use App\Models\User;
use Livewire\Component;
use App\Models\Contractor;
use Livewire\WithPagination;

class ContractorUserManager extends Component
{
    use WithPagination;
    public $contractor_id;
    public $user_id;
    public $contractors = [];
    public $selectedUsers = [];

    public $searchContractor = '';
    public $searchUser = '';

    public $contractor_name;
    public $showContractorDropdown = false;
    public $showOnlySelected = false;

    public function updatedSearchContractor()
    {
        $this->contractors = Contractor::where('contractor_name', 'like', '%' . $this->searchContractor . '%')
            ->orderBy('contractor_name')
            ->limit(30)
            ->get();
        $this->showContractorDropdown = true;
        $this->reset('searchUser', 'selectedUsers');
    }

    public function selectContractor($id, $name)
    {
        $this->contractor_id = $id;
        $this->contractor_name = $name;
        $this->searchContractor = $name;
        $this->showContractorDropdown = false;

        // load user yg sudah terkait
        $this->selectedUsers = Contractor::find($id)->users()->pluck('user_id')->toArray();
    }

    public function updatedSearchUser()
    {
        if ($this->contractor_id) {
            $this->resetPage();
        }
    }

    public function toggleUser($id)
    {
        if (in_array($id, $this->selectedUsers)) {
            $this->selectedUsers = array_diff($this->selectedUsers, [$id]);
        } else {
            $this->selectedUsers[] = $id;
        }
    }

    public function save()
    {
        $contractor = Contractor::find($this->contractor_id);
        $contractor->users()->sync($this->selectedUsers);

        $this->dispatch('alert', [
            'text' => 'Relasi contractor-user berhasil disimpan!',
            'duration' => 5000,
            'close' => true,
        ]);
    }
    public function render()
    {
        // Jika "Hanya Terpilih" aktif dan ada user terpilih
        if ($this->showOnlySelected && count($this->selectedUsers) > 0) {
            $query = User::whereIn('id', $this->selectedUsers)
                ->orderBy('name', 'ASC');

            // tanpa paginate agar semua tampil
            $users = $query->get();
        } else {
            // default mode dengan pencarian dan pagination
            $query = User::query();

            if ($this->searchUser) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . trim($this->searchUser) . '%')
                        ->orWhere('username', 'like', '%' . trim($this->searchUser) . '%');
                });
            }

            $users = $query->orderBy('name', 'ASC')->paginate(100);
        }
        return view('livewire.administration.relasi-cont-user.contractor-user-manager', [
            'users' => $users,
        ]);
    }
}
