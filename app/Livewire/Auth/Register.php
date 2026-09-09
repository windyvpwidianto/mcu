<?php

namespace App\Livewire\Auth;

use App\Helpers\MailHelper;
use App\Mail\RequestUserLoginMail;
use App\Models\Contractor;
use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $name = '';
    public string $name_req = '';
    public string $email_req = '';
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $no_id = '';
    public string $jenis_kelamin = '';
    public $status = 'department'; // default departemen
    public $departments = [], $showDepartemenDropdown = false, $searchDepartemen = '';
    public $contractors = [], $showContractorDropdown = false, $searchContractor = '';
    public $check_no_id_status = ''; // Menyimpan status pengecekan
    public $check_id = '';



    /**
     * Handle an incoming registration request.
     */
    protected $messages =
    [
        'searchDepartemen.required_if' => 'Departemen wajib diisi.',
        'searchContractor.required_if' => 'Kontraktor wajib diisi.',
        'jenis_kelamin.required' => 'Jenis Kelamin wajib diisi.',
    ];
    public function checkId()
    {
        // Panggil fungsi pengecekan
        $exists = User::where('employee_id', $this->check_id)->exists();

        if ($exists) {
            $this->check_no_id_status = 'Nomor ID sudah terdaftar.';
        } else {
            $this->check_no_id_status = 'Nomor ID belum terdaftar.';
        }
    }
    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['first_name', 'last_name'])) {
            $this->updateNameField();
        }
    }
    protected function updateNameField(): void
    {
        // 1. Pemformatan first_name: Title Case (Yoman Denis)
        $firstName = trim($this->first_name);
        // Ubah ke lowercase dulu, lalu capitalize setiap kata
        $formattedFirstName = ucwords(strtolower($firstName));

        // 2. Pemformatan last_name: Uppercase (BANEA)
        $lastName = trim($this->last_name);
        $formattedLastName = strtoupper($lastName);

        // 3. Gabungkan ke properti 'name' (Format: MARGA, Nama Depan)
        if (!empty($formattedLastName) && !empty($formattedFirstName)) {
            $this->name = "{$formattedLastName}, {$formattedFirstName}";
        } elseif (!empty($formattedLastName)) {
            // Jika hanya Marga yang diisi
            $this->name = $formattedLastName;
        } elseif (!empty($formattedFirstName)) {
            // Jika hanya Nama Depan yang diisi
            $this->name = $formattedFirstName;
        } else {
            $this->name = '';
        }
    }
    public function updatedStatus($value)
    {
        if ($value === 'department') {
            // Reset kontraktor jika pindah ke departemen
            $this->resetErrorBag(['searchContractor']);
            $this->reset(['searchContractor', 'contractors']);
        }
        if ($value === 'company') {
            // Reset departemen jika pindah ke kontraktor
            $this->resetErrorBag(['searchDepartemen']);
            $this->reset(['searchDepartemen', 'departments']);
        }
    }


    public function updatedSearchDepartemen()
    {
        if (strlen($this->searchDepartemen) > 1) {
            $this->departments = Department::where('department_name', 'like', '%' . $this->searchDepartemen . '%')
                ->orderBy('department_name')
                ->limit(10)
                ->get();
            $this->showDepartemenDropdown = true;
        } else {
            $this->departments = [];
            $this->showDepartemenDropdown = false;
        }
    }
    public function selectDepartment($id, $name)
    {
        $this->reset('searchContractor');
        $this->searchDepartemen = $name;
        $this->showDepartemenDropdown = false;
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
        $this->reset('searchDepartemen');

        $this->searchContractor = $name;
        $this->showContractorDropdown = false;
    }
    public function register(): void
    {
        // Panggil ini lagi untuk memastikan properti 'name' final sebelum validasi
        $this->updateNameField();
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'no_id' => ['required', 'string', 'max:50', 'unique:users,employee_id'], // Ubah kolom DB jika beda
            'jenis_kelamin' => ['required', 'string', 'in:Laki-Laki,Perempuan'],
            // Validasi wajib isi salah satu (Departemen atau Kontraktor)
            // Kita wajibkan $searchDepartemen jika $status=='department' DAN $searchContractor jika $status=='company'
            'searchDepartemen' => ['required_if:status,department', 'string', 'nullable', 'max:255'],
            'searchContractor' => ['required_if:status,company', 'string', 'nullable', 'max:255'],
            // End Validasi wajib
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        // Tentukan nilai department_name dari input yang aktif
        $departmentName = ($this->status === 'department') ? $this->searchDepartemen : $this->searchContractor;
        // --- LOGIKA BARU UNTUK MENGATASI ERROR DATA TRUNCATED ---
        $genderCode = match ($validated['jenis_kelamin']) {
            'Laki-Laki' => 'L',
            'Perempuan' => 'P',
            default => null, // Tambahkan penanganan jika ada nilai tak terduga
        };
        // Siapkan data untuk User::create
        $dataToCreate = [
            'name'            => $validated['name'],
            'username'        => $validated['username'],
            'email'           => $validated['email'],
            'employee_id'     => $validated['no_id'],
            'gender'          => $genderCode,
            'password'        => Hash::make($validated['password']),
            'department_name' => $departmentName,
        ];

        // 1. Simpan user ke database dan tampung object-nya di variabel $user
        $user = User::create($dataToCreate);

        // 2. Trigger event Laravel (Optional jika Anda menggunakan listener bawaan)
        event(new Registered($user));

        // 3. Ambil ID para Administrator
        $adminIds = User::whereHas('roles', fn($q) => $q->where('name', 'Administrator'))->pluck('id')->toArray();

        // 4. Kirim email jika admin ditemukan
        if (!empty($adminIds)) {
            foreach ($adminIds as $adminId) {
                MailHelper::sendToUserId(
                    $adminId,
                    'Notifikasi Pendaftaran User Baru',
                    'emails.notification',
                    [
                        'subject'        => 'User Baru Terdaftar',
                        'title'          => 'Informasi Akun Baru',
                        'messageText'    => "Seorang user baru telah didaftarkan ke dalam sistem. Berikut adalah detailnya:",
                        'additionalInfo' => "Nama: " . $user->name . "\n" . // Bisa pakai $user atau $validated
                            "Username: " . $user->username . "\n" .
                            "Email: " . $user->email . "\n" .
                            "ID Karyawan: " . $user->employee_id . "\n" .
                            "Jenis Kelamin: " . $genderCode . "\n" .
                            "Departemen: " . $departmentName,
                        // 5. MENGAMBIL ID USER BARU: Gunakan $user->id
                        'actionUrl'      => route('people.details', $user->id)
                    ]
                );
            }
        }

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
    public function requestUserLogin()
    {
        $this->validate([
            'email_req' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'name_req' => ['required', 'string', 'max:255']
        ], [

            'email_req.required' => 'Email wajib diisi untuk request pembuatan user login.',
            'name_req.required' => 'Nama Lengkap wajib diisi untuk request pembuatan user login.',
            'email_req.email' => 'Format email tidak valid.',
        ]);
        try {
            // Kirim email ke Admin (atau ke user itu sendiri jika maksudnya konfirmasi)
            Mail::to('yoman.banea@archimining.com')->send(new RequestUserLoginMail($this->email_req, $this->name_req, $this->check_id));

            $this->reset('email_req', 'name_req');

            // Menggunakan Flux notification jika Anda sudah menginstalnya,
            // atau tetap menggunakan session flash.

            session()->flash('status', __('Request telah dikirim, Silahakan menunggu balasan email dari Admin Tosar!!!'));
            $this->reset(['email_req', 'name_req']);
        } catch (\Exception $e) {
            $this->dispatch(
                'alert',
                [
                    'text' => "Gagal mengirim email request user login.",
                    'duration' => 5000,
                    'destination' => '/contact',
                    'newWindow' => true,
                    'close' => true,
                    'backgroundColor' => "linear-gradient(to right, #ff3333, #ff6666)",
                ]
            );
        }
    }
}
