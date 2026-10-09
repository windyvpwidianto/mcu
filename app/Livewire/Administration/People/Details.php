<?php

namespace App\Livewire\Administration\People;

use App\Models\Contractor;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Details extends Component
{
    public $userId, $name_user;
    public $name, $gender, $date_birth, $username, $dep_cont, $employee_id, $date_commenced, $email, $role_id, $phone_number;
    public $showModal = false;
    public $showDeleteModal = false;
    public $showImportModal = false; // 🔹 untuk modal import
    public $file;
    // Property untuk menampilkan hasil
    public $importedCount = 0;
    public $skippedCount = 0;
    public $selectedUsers = []; // simpan user yang dicentang
    public $selectAll = false; // untuk checkbox master
    public $showBulkUpdateModal = false;
    public $bulkRole;
    public $roles;
    public $searchTerm = '';
    public $deptCont = 'department';
    public $search = '';
    public $departments = [];
    public $contractors = [];
    public $showDropdown = false;
    public $searchContractor = '';
    public $showContractorDropdown = false;
    public $password;
    public $password_confirmation;
    #[Validate('required_without:contractor_id')]
    public $department_id;
    #[Validate('required_without:department_id')]
    public $contractor_id;

    protected function rules()
    {
        $userId = $this->userId ?? 0;
        // Tentukan status required berdasarkan keberadaan userId
        $isRequired = $userId ? 'nullable' : 'required';

        return [
            'name' => 'required|string|max:255',
            'gender' => 'nullable|in:L,P',
            'date_birth' => 'nullable|date',
            'role_id' => 'nullable',
            'phone_number' => 'nullable|string|max:20',
            'dep_cont' => 'nullable|string|max:255',
            'date_commenced' => 'nullable|date',

            // Username: nullable jika edit, required jika baru
            'username' => [
                $isRequired,
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($userId),
            ],

            // Employee ID: nullable jika edit, required jika baru
            'employee_id' => [
                $isRequired,
                'string',
                'max:255',
                Rule::unique('users', 'employee_id')->ignore($userId),
            ],

            // Email: nullable jika edit, required jika baru
            'email' => [
                $isRequired,
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'password' => [
                $isRequired,
                'string',
                'min:6',
                'confirmed',
            ],

            'password_confirmation' => [
                $isRequired,
                'string',
                'min:6',
            ],
        ];
    }
    protected function messages()
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'department_id.required_without' => 'Departemen wajib dipilih jika kontraktor tidak diisi.',
            'contractor_id.required_without' => 'Kontraktor wajib dipilih jika departemen tidak diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'employee_id.required' => 'Employee ID wajib diisi.',
            'employee_id.unique' => 'Employee ID sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'date_birth.date' => 'Tanggal lahir harus berupa format tanggal.',
            'date_commenced.date' => 'Tanggal mulai kerja harus berupa format tanggal.',
        ];
    }
    public function render()
    {
        return view('livewire.administration.people.details', [
            'users' => User::search(trim($this->searchTerm))->paginate(20),
            'role' => Role::all()
        ]);
    }

    public function mount($id)
    {
        $user = User::findOrFail($id);

        // Set $this->userId
        $this->userId = $user->id;
        $this->name_user = $user->name;
        $this->fill($user->toArray());

        // 2. Tentukan Radio Button yang terpilih...
        $this->deptCont = $user->pilih_divisi;

        // 3. Memuat nilai nama...
        if ($this->deptCont === 'department') {
            $this->search = $user->department_name;
            $this->department_id = Department::where('department_name', $user->department_name)->value('id');
            $this->contractor_id = null;
            $this->searchContractor = '';
            $this->dep_cont = $user->department_name;
        } elseif ($this->deptCont === 'contractor') {
            $contractorName = $user->company_name ?? $user->department_name;
            $this->searchContractor = $contractorName;
            $this->contractor_id = Contractor::where('contractor_name', $contractorName)->value('id');
            $this->department_id = null;
            $this->search = '';
            $this->dep_cont = $contractorName;
        } else {
            $this->search = $user->department_name;
            $this->searchContractor = '';
            $this->dep_cont = $user->department_name;
        }
    }

    public function updatedDeptCont($value)
    {
        if ($value === 'department') {
            $this->reset('searchContractor', 'contractor_id');
            $this->dep_cont = $this->search;
        } else {
            $this->reset('search', 'department_id');
            $this->dep_cont = $this->searchContractor;
        }
        $this->resetValidation(['department_id', 'contractor_id']);
    }

    public function updatedSearch()
    {
        if (strlen($this->search) > 1) {
            $this->departments = Department::where('department_name', 'like', '%' . $this->search . '%')
                ->orderBy('department_name')
                ->limit(10)
                ->get();
            $this->showDropdown = true;
        } else {
            $this->departments = [];
            $this->showDropdown = false;
        }
    }
    public function selectDepartment($id, $name)
    {
        $this->reset('searchContractor', 'contractor_id');
        $this->department_id = $id;
        $this->search = $name;
        $this->dep_cont = $name;
        $this->showDropdown = false;
        $this->resetValidation(['department_id', 'contractor_id']);
    }
    public function updatedSearchContractor()
    {
        if (strlen($this->searchContractor) > 1) {
            $this->contractors = Contractor::query()
                ->where('contractor_name', 'like', '%' . $this->searchContractor . '%')
                ->orderBy('contractor_name')
                ->limit(10)
                ->get();
            $this->showContractorDropdown = true;
        } else {
            $this->contractors = [];
            $this->showContractorDropdown = true;
        }
    }
    public function selectContractor($id, $name)
    {
        $this->reset('search', 'department_id');
        $this->contractor_id = $id;
        $this->searchContractor = $name;
        $this->dep_cont = $name;
        $this->showContractorDropdown = false;
        $this->resetValidation(['department_id', 'contractor_id']);
    }


    public function save()
    {
        // Normalize empty/whitespace values to null
        $this->username = !empty(trim((string)$this->username)) ? trim((string)$this->username) : null;
        $this->email = !empty(trim((string)$this->email)) ? trim((string)$this->email) : null;
        $this->employee_id = !empty(trim((string)$this->employee_id)) ? trim((string)$this->employee_id) : null;
        $this->phone_number = !empty(trim((string)$this->phone_number)) ? trim((string)$this->phone_number) : null;
        $this->date_birth = !empty($this->date_birth) ? $this->date_birth : null;
        $this->date_commenced = !empty($this->date_commenced) ? $this->date_commenced : null;
        $this->gender = !empty($this->gender) ? $this->gender : null;
        $this->role_id = !empty($this->role_id) ? $this->role_id : null;

        $this->validate();

        $userData = [
            'name' => $this->name,
            'gender' => $this->gender,
            'date_birth' => $this->date_birth,
            'username' => $this->username,
            'role_id' => $this->role_id,
            'pilih_divisi' => $this->deptCont,
            'employee_id' => $this->employee_id,
            'phone_number' => $this->phone_number,
            'date_commenced' => $this->date_commenced,
            'email' => $this->email,
        ];

        if ($this->deptCont === 'department') {
            $userData['department_name'] = !empty($this->dep_cont) ? $this->dep_cont : null;
            $userData['company_name'] = null;
        } else {
            $userData['department_name'] = null;
            $userData['company_name'] = !empty($this->dep_cont) ? $this->dep_cont : null;
        }

        // Logika untuk Password: HANYA perbarui jika field password diisi.
        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        $user = User::findOrFail($this->userId);
        $user->update($userData);

        $this->showModal = false;
        $this->dispatch(
            'alert',
            [
                'text' => 'user berhasil diupdate!',
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]
        );
    }
}
