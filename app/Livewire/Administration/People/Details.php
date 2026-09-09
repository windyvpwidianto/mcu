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
    public $name, $gender, $date_birth, $username, $dep_cont, $employee_id, $date_commenced, $email, $role_id;
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

        // ❗ PINDAHKAN INI KE ATAS: Set $this->userId DULU
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
        } elseif ($this->deptCont === 'contractor') {
            $this->searchContractor = $user->department_name;
            $this->contractor_id = Contractor::where('contractor_name', $user->department_name)->value('id');
            $this->department_id = null;
            $this->search = '';
        } else {
            $this->search = $user->department_name;
            $this->searchContractor = '';
        }
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
        $this->validateOnly('department_id');
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
        $this->validateOnly('contractor_id');
    }


    public function save()
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'gender' => $this->gender,
            'date_birth' => $this->date_birth,
            'username' => $this->username,
            'role_id' => $this->role_id,
            'department_name' => $this->dep_cont, // atau nama kolom yang sesuai
            'pilih_divisi' => $this->deptCont,
            'employee_id' => $this->employee_id,
            'date_commenced' => $this->date_commenced,
            'email' => $this->email,
        ];

        // Logika untuk Password: HANYA perbarui jika field password diisi.
        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        // Asumsi: UserProfile adalah model yang tepat (misalnya App\Models\User atau UserProfile)
        User::whereId($this->userId)
            ->update($userData);


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
