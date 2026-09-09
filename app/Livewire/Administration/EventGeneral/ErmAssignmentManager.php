<?php

namespace App\Livewire\Administration\EventGeneral;

use App\Models\User;
use Livewire\Component;
use App\Models\Contractor;
use App\Models\Department;
use App\Models\ErmAssignment;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;

class ErmAssignmentManager extends Component
{
    #[Validate('required_without:contractor_id')]
    public $department_id;
    public $editId;
    #[Validate('required_without:department_id')]
    public $contractor_id;
    public $status_new = 'department';
    public $status;
    public $assignments, $search = '';
    public $users = [], $showMpderatorDropdown = false, $searchModerator = '';
    public $departments = [], $showDepartemenDropdown = false, $searchDepartemen = '';
    public $contractors = [], $showContractorDropdown = false, $searchContractor = '';
    public $showModeratorDropdown = false;
    // 💡 BARU: Array untuk menampung ID moderator yang dipilih
    #[Validate('required|array', message: 'Anda harus memilih minimal satu moderator.')]
    public $moderator_ids = [];
    public $user_id;
    public $department_search, $contractor_search;
    // 💡 BARU: Array untuk menampung detail moderator yang dipilih (ID dan Nama)
    public $selectedModerators = [];
    protected $messages =
    [
        'user_id.required'                => 'Nama Moderator wajib diisi.',
        'department_id.required_without' => 'Departemen wajib dipilih jika kontraktor tidak diisi.',
        'contractor_id.required_without' => 'Kontraktor wajib dipilih jika departemen tidak diisi.',
    ];
    public function mount()
    {
        $this->loadAssignments();
    }

    public function updatedSearch()
    {
        $this->loadAssignments();
    }
    public function updatedContractorSearch()
    {
        $this->loadAssignments();
    }
    public function updatedDepartmentSearch()
    {
        $this->loadAssignments();
    }

    public function open_modal()
    {
        // Lakukan proses logika di sini (misal: reset form, fetch data)
        $this->reset_data();

        // Kirim event ke frontend untuk membuka modal
        $this->dispatch('open-my-modal');
    }
    public function close_modal()
    {
        // Lakukan proses logika di sini (misal: reset form, fetch data)
        $this->reset_data();

        // Kirim event ke frontend untuk membuka modal
        $this->dispatch('close-my-modal');
    }


    public function close_modal_update()
    {
        $this->reset('editId');
    }
    public function loadAssignments()
    {
        $this->status = 'department';

        $query = ErmAssignment::with(['user', 'department', 'contractor']);

        // Pencarian nama user (Opsional)
        $query->when($this->search, function ($q) {
            $q->whereHas('user', function ($subQ) {
                $subQ->where('name', 'like', '%' . $this->search . '%');
            });
        });

        // Filter Department (Opsional)
        $query->when($this->department_search, function ($q) {
            $q->where('department_id', $this->department_search);
        });

        // Filter Contractor (Opsional)
        $query->when($this->contractor_search, function ($q) {
            $q->where('contractor_id', $this->contractor_search);
        });

        // Eksekusi
        $this->assignments = $query->orderBy('department_id', 'DESC')
            ->orderBy('contractor_id', 'DESC')
            ->get();
    }
    public function updatedStatus($value)
    {
        if ($value === 'department') {
            // Reset kontraktor jika pindah ke departemen
            $this->resetErrorBag(['contractor_id']);
            $this->reset(['contractor_id', 'searchContractor', 'contractors']);
        }
        if ($value === 'company') {
            // Reset departemen jika pindah ke kontraktor
            $this->resetErrorBag(['department_id']);
            $this->reset(['department_id', 'searchDepartemen', 'departments']);
        }
    }
    public function updatedSearchModerator()
    {
        // 💡 BARU: Kecualikan ID yang sudah dipilih dari hasil pencarian
        $exclude_ids = $this->moderator_ids;

        if (strlen($this->searchModerator) > 1) {
            $this->users = User::where('name', 'like', '%' . $this->searchModerator . '%')
                // Menghindari menampilkan yang sudah dipilih
                ->whereNotIn('id', $exclude_ids)
                ->orderBy('name')
                ->limit(100)
                ->get();
            // PERBAIKAN: Menggunakan properti yang benar
            $this->showModeratorDropdown = true;
        } else {
            $this->users = [];
            // PERBAIKAN: Menggunakan properti yang benar
            $this->showModeratorDropdown = false;
        }
    }

    public function selectModerator($id, $name)
    {
        // 1. Pengecekan agar ID tidak ganda

        if (!is_null($this->editId)) {

            $this->user_id = $id;
            $this->searchModerator = $name;
            $this->showModeratorDropdown = false;
        } else {
            if (!in_array($id, $this->moderator_ids)) {

                // 2. Tambahkan ke array lokal untuk UI
                $this->moderator_ids[] = (int) $id;
                $this->selectedModerators[] = [
                    'id' => $id,
                    'name' => $name,
                ];
            }
            // Reset input pencarian
            $this->reset('searchModerator', 'users');
            $this->showModeratorDropdown = false;
        }
    }
    // 💡 BARU: Metode untuk menghapus moderator yang sudah dipilih
    public function removeModerator($id)
    {
        // 1. Hapus ID dari array moderator_ids
        $this->moderator_ids = array_diff($this->moderator_ids, [(int) $id]);
        // 2. Hapus detail moderator dari array selectedModerators
        $this->selectedModerators = collect($this->selectedModerators)->filter(function ($moderator) use ($id) {
            return $moderator['id'] != $id;
        })->values()->toArray(); // values() untuk mereset kunci array

        // Opsional: Lakukan pencarian ulang jika pengguna sedang mencari
        $this->updatedSearchModerator();
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
        $this->department_id = $id;
        $this->searchDepartemen = $name;
        $this->showDepartemenDropdown = false;
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
        $this->reset('searchDepartemen', 'department_id');
        $this->contractor_id = $id;
        $this->searchContractor = $name;
        $this->showContractorDropdown = false;
        $this->validateOnly('contractor_id');
    }
    // 💡 FUNGSI ASSIGN YANG DISESUAIKAN
    public function assign()
    {
        // 1. Validasi Properti Dasar (required, array)
        $this->validate();
        $successfulAssignments = 0;
        $failedAssignments = 0;
        $failedNames = [];

        // Tentukan kolom dan nilai ID level yang sedang aktif
        $levelColumn = $this->status_new === 'department' ? 'department_id' : 'contractor_id';
        $levelValue = $this->status_new === 'department' ? $this->department_id : $this->contractor_id;

        // 2. Persiapan: Ambil ID user yang sudah terdaftar dengan KOMBINASI LENGKAP ini
        $existingAssignments = ErmAssignment::where($levelColumn, $levelValue)
            ->pluck('user_id')
            ->toArray();

        // 3. Mulai Transaksi
        DB::beginTransaction();

        try {
            // 4. Iterasi setiap ID moderator yang dipilih
            foreach ($this->moderator_ids as $userId) {

                // 5. Pengecekan Duplikasi EFEKTIF
                // Cek apakah ID saat ini sudah ada di daftar $existingAssignments
                if (in_array($userId, $existingAssignments)) {

                    // TIDAK MEMUNCULKAN SESSION FLASH ERROR
                    $failedAssignments++;

                    // Catat nama yang gagal
                    $user = User::find($userId);
                    if ($user) {
                        $failedNames[] = $user->name;
                    }
                    // Langsung lanjut ke ID berikutnya (mengabaikan ID yang duplikat)
                    continue;
                }
                ErmAssignment::create([
                    'user_id' => $userId,
                    // Pastikan hanya ID yang relevan yang diisi, yang lain null/default
                    'department_id' => $this->status_new === 'department' ? $this->department_id : null,
                    'contractor_id' => $this->status_new === 'company' ? $this->contractor_id : null,
                ]);

                $successfulAssignments++;
            }

            // 7. Commit Transaksi
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            // Hanya memunculkan error jika terjadi kegagalan SISTEM
            session()->flash('error', 'Terjadi kesalahan sistem saat menyimpan data.');
            return;
        }
        // 8. Pengiriman Notifikasi dan Reset State
        // Gabungkan pesan notifikasi untuk memberitahu user ID mana yang berhasil/gagal
        if ($successfulAssignments > 0) {
            $message = "Berhasil menetapkan **{$successfulAssignments}** ERM.";
            $backgroundColor = "linear-gradient(to right, #06b6d4, #22c55e)";

            if ($failedAssignments > 0) {
                $message .= " **{$failedAssignments}** ERM dilewati (sudah terdaftar): " . implode(', ', $failedNames);
                $backgroundColor = "linear-gradient(to right, #f59e0b, #ef4444)";
            }

            $this->dispatch('alert', [
                'text' => $message,
                'duration' => 8000,
                'backgroundColor' => $backgroundColor,
            ]);
        } elseif ($failedAssignments > 0) {
            // Jika semua ID duplikat
            session()->flash('error', 'Semua ERM yang dipilih sudah terdaftar untuk level dan tipe bahaya ini: ' . implode(', ', $failedNames));
        }
        // Reset properti
        $this->reset_data();
        $this->loadAssignments();
        $this->dispatch('close-my-modal');
    }
    public function edit($id)
    {
        $item = ErmAssignment::whereId($id)->first();
        $this->editId = $id;
        $this->user_id = $item->user_id;
        $this->searchModerator = $item->user->name;
        $this->department_id = $item->department_id;
        $this->contractor_id = $item->contractor_id;
        $this->status =  !empty($this->contractor_id) ? 'company'  : 'department';
        if ($this->department_id) {
            $this->searchDepartemen = $item->department->department_name;
        } else {
            $this->searchContractor = $item->contractor->contractor_name;
        }
    }
    public function update()
    {
        // 1. Perbaiki Validasi
        $this->validate([
            'user_id'       => 'required|exists:users,id', // 'exists' memastikan ID benar-benar ada di tabel users
            'department_id' => $this->status === 'department' ? 'required' : 'nullable',
            'contractor_id' => $this->status === 'company' ? 'required' : 'nullable',
        ]);
        // 2. Ambil data dengan findOrFail
        $assignment = ErmAssignment::findOrFail($this->editId);
        // 3. Update data
        $assignment->update([
            'user_id'       => $this->user_id,
            'department_id' => $this->status === 'department' ? $this->department_id : null,
            'contractor_id' => $this->status === 'company' ? $this->contractor_id : null,
        ]);
        // 4. Tutup Modal & Berikan Feedback
        $this->loadAssignments();
        $this->dispatch('alert', [
            'text' => "Data berhasil diupdate!",
            'duration' => 8000,
            'backgroundColor' => "linear-gradient(to right, #f59e0b, #ef4444)",
        ]);
        $this->reset('editId');
    }
    public function delete($id)
    {
        ErmAssignment::findOrFail($id)->delete();
        $this->dispatch('alert', [
            'text' => "Penugasan ERM berhasil dihapus.",
            'duration' => 8000,
            'backgroundColor' => "linear-gradient(to right, #f59e0b, #ef4444)",
            // ... properti dispatch lainnya
        ]);
        $this->loadAssignments();
    }
    public function reset_data()
    {
        $this->reset(['moderator_ids', 'selectedModerators', 'searchModerator', 'department_id', 'contractor_id']);
    }
    public function render()
    {
        return view('livewire.administration.event-general.erm-assignment-manager', [
            'department_all' => Department::all(),
            'contractor_all' => Contractor::all()
        ]);
    }
}
