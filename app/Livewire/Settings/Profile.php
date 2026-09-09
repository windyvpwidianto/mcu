<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Livewire\Component;
use App\Models\Contractor;
use App\Models\Department;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Profile extends Component
{
    public string $name = '';
    public string $username = '';
    public string $department_name = '';
    public string $email = '';
    public $deptCont = 'department';
    public $search = '';
    public $date_birth;
    public $employee_id;
    public $dep_cont = '';
    public $departments = [];
    public $contractors = [];
    public $showDropdown = false;
    public $searchContractor = '';
    public $showContractorDropdown = false;

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date_birth' => 'nullable|date',
            'employee_id' => 'nullable|string',
            'username' => ['nullable', 'string', 'max:255'],
            'department_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore(Auth::id())]
        ];
    }

    public function messages()
    {
        return [
            // name
            'name.required' => 'Nama wajib diisi.',
            // email
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            'department_name.required' => 'Department/Contractor wajib dipilih.',
        ];
    }
    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $dep = Department::where('department_name', Auth::user()->department_name)->exists();
        $cont = Contractor::where('contractor_name', Auth::user()->department_name)->exists();

        if ($dep) {
            $this->search = Auth::user()->department_name;
            $this->deptCont = 'department';
        } elseif ($cont) {
            $this->searchContractor = Auth::user()->department_name;
            $this->deptCont = 'contractor';
        }
        $this->username = Auth::user()->username;
        $this->name = Auth::user()->name;
        $this->department_name = Auth::user()->department_name;
        $this->employee_id = Auth::user()->employee_id;
        $this->date_birth = Auth::user()->date_birth;
        $this->email = Auth::user()->email;
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
        $this->reset('searchContractor');
        $this->department_name = $name;
        $this->search = $name;
        $this->dep_cont = $name;
        $this->showDropdown = false;
        $this->validateOnly('department_name');
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
            $this->showContractorDropdown = false;
        }
    }
    public function selectContractor($id, $name)
    {
        $this->reset('search');
        $this->department_name = $name;
        $this->searchContractor = $name;
        $this->dep_cont = $name;
        $this->showContractorDropdown = false;
        $this->validateOnly('department_name');
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();
        // Validasi semua field
        $validated = $this->validate();
        // Update hanya field yang kamu pakai
        $user->name = $validated['name'];
        $user->username = $validated['username'] ?? $user->username;
        $user->department_name = $validated['department_name'];
        $user->employee_id = $validated['employee_id'];
        $user->date_birth = $validated['date_birth'];

        // Jika email berubah → reset verifikasi
        if ($validated['email'] !== $user->email) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
        }

        // Simpan perubahan
        $user->save();

        // Emit ke Livewire
        $this->dispatch('profile-updated', name: $user->name);
    }


    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}
