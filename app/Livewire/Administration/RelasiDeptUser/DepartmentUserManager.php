<?php

namespace App\Livewire\Administration\RelasiDeptUser;

use App\Models\User;
use Livewire\Component;
use App\Models\Department;
use Livewire\WithPagination;

class DepartmentUserManager extends Component
{
    use WithPagination;
    public $department_id;
    public $user_id;
    public $departments = [];
    public $selectedUsers = [];

    public $searchDepartment = '';
    public $searchUser = '';

    public $department_name;
    public $showDepartmentDropdown = false;
    public $showOnlySelected = false;

    public function updatedSearchDepartment()
    {
        $this->departments = Department::where('department_name', 'like', '%' . $this->searchDepartment . '%')
            ->orderBy('department_name')
            ->limit(30)
            ->get();
        $this->showDepartmentDropdown = true;
        $this->reset('searchUser', 'selectedUsers');
    }
    public function selectDepartment($id, $name)
    {
        $this->department_id = $id;
        $this->department_name = $name;
        $this->searchDepartment = $name; // tampilkan nama di input
        $this->showDepartmentDropdown = false;
        // Pilih department → load user yang sudah terkait
        $this->selectedUsers = Department::find($id)->users()->pluck('user_id')->toArray();
    }
    public function updatedSearchUser()
    {
        if ($this->department_id) {
            $this->resetPage();
        }
    }
    // Toggle user di selectedUsers
    public function toggleUser($id)
    {
        if (in_array($id, $this->selectedUsers)) {
            $this->selectedUsers = array_diff($this->selectedUsers, [$id]);
        } else {
            $this->selectedUsers[] = $id;
        }
    }
    // Simpan relasi ke pivot
    public function save()
    {
        $department = Department::find($this->department_id);
        $department->users()->sync($this->selectedUsers);
        $this->dispatch(
            'alert',
            [
                'text' => 'Relasi user berhasil disimpan!',
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]
        );
        $this->reset('searchUser', 'selectedUsers','searchDepartment','department_id','department_name');
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

        return view('livewire.administration.relasi-dept-user.department-user-manager', [
            'users' => $users,
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
