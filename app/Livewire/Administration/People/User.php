<?php

namespace App\Livewire\Administration\People;

use App\Models\Role;
use Livewire\Component;
use App\Models\Contractor;
use App\Models\Department;
use App\Imports\UsersImport;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use App\Models\User as UserProfile;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Validation\Rule; // <-- BARIS INI DITAMBAHKAN

class User extends Component
{
    use WithPagination, WithFileUploads;

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
    public $searchPeople;
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
    public function mount()
    {
        $this->roles = Role::all(); // pakai model role kamu
    }
    // 🔹 Jalankan validasi realtime
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUsers = UserProfile::pluck('id')->toArray(); // pilih semua
        } else {
            $this->selectedUsers = [];
        }
    }
    public function import()
    {
        // 1. Validasi Format File
        $this->validate([
            'file' => 'required|mimes:xlsx,csv,xls|max:2048',
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes'    => 'Format file harus xlsx, csv, atau xls.',
            'file.max'      => 'Ukuran file maksimal 2MB.',
        ]);

        $imported = 0;
        $skipped = 0;

        try {
            // 2. Membungkus Logika Impor dalam Try-Catch
            $rows = Excel::toCollection(new UsersImport, $this->file->getRealPath());

            foreach ($rows[0] as $row) {
                $user = (new UsersImport)->model($row->toArray());

                // Pastikan Anda menangani logika duplikasi/lewat di dalam model()
                if ($user) {
                    $user->save();
                    $imported++;
                } else {
                    $skipped++;
                }
            }

            // 3. Logika Sukses (Hanya dijalankan jika tidak ada exception)
            $this->importedCount = $imported;
            $this->skippedCount = $skipped;

            $this->reset('file');
            $this->showImportModal = false;

            session()->flash('success', "$imported row berhasil diimport, $skipped row dilewati (kosong/duplikat).");
            $this->dispatch(
                'alert',
                [
                    'text' => "Data user berhasil diimport!",
                    'duration' => 5000,
                    'destination' => '/contact',
                    'newWindow' => true,
                    'close' => true,
                    'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
                ]
            );
        } catch (ValidationException $e) {
            // 4. Menangkap Error Validasi Data Internal

            $failures = $e->failures();
            $errorHtml = '<ul>';
            $totalErrors = count($failures);

            // Kumpulkan pesan kegagalan ke dalam format HTML
            foreach ($failures as $failure) {
                $rowNumber = $failure->row();
                $errors = implode(', ', $failure->errors());

                $errorHtml .= "<li>Baris {$rowNumber}: {$errors}</li>";
            }
            $errorHtml .= '</ul>';

            // Reset dan tutup modal
            $this->reset('file');
            $this->showImportModal = false;

            // Tampilkan alert dengan detail error validasi
            $this->dispatch(
                'alert',
                [
                    'title' => "Gagal Impor ({$totalErrors} Baris Tidak Valid)",
                    'text' => 'Terdapat kesalahan data di dalam file Excel Anda. Mohon periksa detail berikut.',
                    'type' => 'error', // Pastikan notifikasi Anda mendukung tipe 'error'
                    'html' => $errorHtml,
                    'backgroundColor' => "background: linear-gradient(135deg, #f44336, #d32f2f);",
                    'close' => true,
                ]
            );
        } catch (\Exception $e) {
            // 5. Menangkap error lain (Database/Server)
            $this->reset('file');
            $this->showImportModal = false;

            // Anda bisa log error ini di sini

            $this->dispatch(
                'alert',
                [
                    'title' => "Terjadi Kesalahan Server",
                    'text' => 'Gagal memproses file. Periksa log server atau coba lagi.',
                    'type' => 'error',
                    'backgroundColor' => "background: linear-gradient(135deg, #f44336, #d32f2f);",
                    'close' => true,
                ]
            );
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


    public function render()
    {
        return view('livewire.administration.people.user', [
            'users' => UserProfile::search($this->searchPeople)->paginate(20),
            'role' => Role::all()
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
    public function create()
    {
        $this->resetInput();
        $this->showModal = true;
    }
    public function edit($id)
    {
        $user = UserProfile::findOrFail($id);

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

        $this->showModal = true;
        $this->dispatch('dateLoaded');
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
        UserProfile::create(
            $userData
        );
        $this->resetInput();
        $this->showModal = false;
        $text = $this->userId ? 'user berhasil diupdate!' : 'user berhasil ditambahkan!';
        $this->dispatch(
            'alert',
            [
                'text' =>  $text,
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]
        );
    }

    public function confirmDelete($id)
    {
        $this->userId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        UserProfile::findOrFail($this->userId)->delete();
        $this->resetInput();
        $this->showDeleteModal = false;
        $this->dispatch(
            'alert',
            [
                'text' => "User berhasil dihapus!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #f44336, #d32f2f);",
            ]
        );
    }

    public function bulkUpdate()
    {
        $this->validate([
            'bulkRole' => 'required|exists:roles,id',
        ]);

        UserProfile::whereIn('id', $this->selectedUsers)
            ->update(['role_id' => $this->bulkRole]);

        $this->reset(['selectedUsers', 'selectAll', 'bulkRole']);
        $this->showBulkUpdateModal = false;

        $this->dispatch(
            'alert',
            [
                'text' => "Bulk update berhasil!",
                'duration' => 5000,
                'backgroundColor' => "background: linear-gradient(135deg, #2196f3, #1976d2);",
            ]
        );
    }

    private function resetInput()
    {
        $this->reset(['userId', 'name', 'gender', 'date_birth', 'username', 'role_id', 'employee_id', 'date_commenced', 'email', 'dep_cont', 'deptCont', 'password', 'password_confirmation',]);
        $this->dispatch('dateLoaded');
    }
}
