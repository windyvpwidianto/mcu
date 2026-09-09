<?php

namespace App\Livewire\Hazard;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Hazard;
use Livewire\Component;
use App\Models\EventType;
use App\Models\Contractor;
use App\Models\Department;
use App\Enums\HazardStatus;
use App\Models\EventSubType;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HazardExport;

class HazardReportPanel extends Component
{
    use WithPagination;
    // PROPERTI BARU UNTUK CHECKBOX
    public bool $filterByAuth = false;
    public array $filterStatus = [];
    public $role;
    // public $filterEventType;
    public $openDropdownId = null;
    public $deptCont = 'department'; // default departemen
    public $search = '';
    public $searchPelapor = '';
    public $searchContractor = '';
    public $showDropdown = false;
    public $showContractorDropdown = false;
    public $showPelaporDropdown = false;
    public $pelapors = [];
    public $departments = [];
    public $contractors = [];
    public $pelapor_id;
    public $department_id;
    public $contractor_id;
    public $action_due_date = '';
    public $start_date;
    public $end_date;
    // Properti ini yang akan mengontrol tampilan dropdown
    public bool $isDropdownOpen = false;
    // Properties untuk menampung ID yang dicentang
    public array $filterDepartment = [];
    public array $filterContractor = [];
    public array $filterEventType = [];
    public array $filterEventSubType = [];
    public array $filterReporterDept = []; // Properti baru
    // Data filter
    public $filterOptions = [];
    public function getRandomBadgeColor($status)
    {
        $map = [
            'cancelled'   => 'badge-error',   // Tulis lengkap
            'closed'      => 'badge-success',
            'in_progress' => 'badge-warning',
            'pending'     => 'badge-accent',
            'submitted'   => 'badge-info',
        ];

        return $map[$status] ?? 'badge-neutral';
    }
    public function mount()
    {
        $eventTypes = Hazard::select('event_type_id')
            ->get()
            ->pluck('event_type_id')
            ->toArray();

        // 1. Ambil semua nama departemen dan kontraktor sebagai referensi filter
        $departmentNames = Department::pluck('department_name');
        $contractorNames = Contractor::pluck('contractor_name');

        // 2. Gabungkan keduanya menjadi satu koleksi nama yang diizinkan
        $allowedNames = $departmentNames->concat($contractorNames)->unique()->filter();

        // Muat data untuk filter
        $this->filterOptions = [
            'Department' => Department::all(['id', 'department_name']),
            'Contractors' => Contractor::all(['id', 'contractor_name']),
            'EventType' => EventType::where('event_type_name', 'like', '%' . 'hazard' . '%')->get(['id', 'event_type_name']),
            'EventSubType' => EventSubType::whereIn('event_type_id', $eventTypes)->get(['id', 'event_sub_type_name']),

            // 3. Filter ReporterDepartments hanya yang ada di list allowedNames
            'ReporterDepartments' => User::whereIn('department_name', $allowedNames)
                ->selectRaw("DISTINCT department_name as dept")
                ->orderBy('dept', 'asc')
                ->pluck('dept'),
        ];
    }
    public function updatingSearch()
    {
        $this->resetPage(); // Reset pagination ketika search berubah
    }
    public function updatingFilterStatus()
    {
        $this->resetPage(); // Reset pagination ketika filter status berubah
    }
    public function updatingFilterEventType()
    {
        $this->resetPage(); // Reset pagination ketika filter event type berubah
    }
    public function updatingFilterByAuth()
    {
        $this->resetPage(); // Reset pagination ketika filter checkbox berubah
    }
    public function updatingFilterDepartment()
    {
        $this->resetPage(); // Reset pagination ketika filter department berubah
    }
    public function updatingFilterContractor()
    {
        $this->resetPage(); // Reset pagination ketika filter contractor berubah
    }
    public function updatingFilterEventSubType()
    {
        $this->resetPage(); // Reset pagination ketika filter event sub type berubah
    }
    public function updatingActionDueDate()
    {
        $this->resetPage(); // Reset pagination ketika filter tanggal berubah
    }
    public function updatingSearchPelapor()
    {
        $this->resetPage(); // Reset pagination ketika search pelapor berubah
    }
    public function updatingSearchContractor()
    {
        $this->resetPage(); // Reset pagination ketika search contractor berubah
    }
    public function toggleDropdownstatus()
    {
        $this->isDropdownOpen = !$this->isDropdownOpen;
    }
    public function updatingFilterReporterDept()
    {
        $this->resetPage();
    }
    public function updatedFilterStatus()
    {
        // Panggil logika filter data Anda di sini
        // $this->filterData();

        // Opsional: Tutup dropdown setelah filter diterapkan
        // $this->isDropdownOpen = false;
    }
    public function updatedDeptCont($value)
    {
        if ($value === 'department') {
            // Reset kontraktor jika pindah ke departemen
            $this->resetErrorBag(['contractor_id']);
            $this->reset(['contractor_id', 'searchContractor', 'contractors']);
        }
        if ($value === 'company') {
            // Reset departemen jika pindah ke kontraktor
            $this->resetErrorBag(['department_id']);
            $this->reset(['department_id', 'search', 'departments']);
        }
    }
    public function updatedActionDueDate($value)
    {
        // Cek apakah nilai tidak kosong
        if (!empty($value)) {
            // Pisahkan string berdasarkan " to "
            $dates = explode(' to ', $value);

            // Pastikan ada dua tanggal yang valid
            if (count($dates) === 2) {
                $this->start_date = $dates[0];
                $this->end_date = $dates[1];
            }
        } else {
            $this->reset('start_date', 'end_date');
        }
    }
    public function toggleDropdown($reportId)
    {
        $this->openDropdownId = $this->openDropdownId === $reportId ? null : $reportId;
    }
    public function updateStatus($reportId, $newStatus)
    {
        $report = Hazard::findOrFail($reportId);
        $userRole = Auth::user()->role;

        $valid = match ([$userRole, $report->status->value, $newStatus]) {
            // Moderator: kirim ke ERM
            ['moderator', 'submitted', 'in_progress'] => true,

            // ERM: kembalikan ke moderator
            ['erm', 'in_progress', 'pending'] => true,
            ['erm', 'in_progress', 'closed'] => true,

            // Moderator: tutup laporan
            ['moderator', 'pending', 'closed'] => true,

            // Moderator: kirim ulang ke ERM
            ['moderator', 'pending', 'in_progress'] => true,

            // Moderator: batalkan
            ['moderator', 'submitted', 'cancelled'],
            ['moderator', 'pending', 'cancelled'] => true,
            // Moderator: buka kembali report
            ['moderator', 'closed', 'in_progress'] => true,
            ['moderator', 'cancelled', 'submitted'] => true,
            ['moderator', 'cancelled', 'closed'] => true,
            default => false,
        };

        if (! $valid) {
            session()->flash('message', 'Aksi tidak diizinkan untuk status/role saat ini.');
            return;
        }
        // prevent non-moderator from reopening closed
        if ($report->status === HazardStatus::Closed && $userRole !== 'moderator') {
            abort(403, 'Hanya moderator yang dapat membuka kembali laporan yang sudah ditutup.');
        }

        $report->status = $newStatus;
        $report->save();

        // TODO: kirim notifikasi otomatis

        session()->flash('message', "Status laporan #{$report->id} diubah menjadi {$newStatus}.");
    }
    protected function filterModeratorReports($query)
    {
        $user = Auth::user();

        $assignedDept = $user->moderatorAssignments->pluck('department_id')->filter()->unique();
        $assignedContractors = $user->moderatorAssignments->pluck('contractor_id')->filter()->unique();
        $assignedCompanies = $user->moderatorAssignments->pluck('company_id')->filter()->unique();

        $companyDept = \App\Models\Department::whereIn('company_id', $assignedCompanies)->pluck('id');
        $companyContractor = \App\Models\Contractor::whereIn('company_id', $assignedCompanies)->pluck('id');

        $allDept = $assignedDept->merge($companyDept)->unique();
        $allContractors = $assignedContractors->merge($companyContractor)->unique();

        $query->where(function ($q) use ($allDept, $allContractors, $assignedCompanies) {
            $q->when($allDept->isNotEmpty(), fn($q) => $q->whereIn('department_id', $allDept))
                ->when($allContractors->isNotEmpty(), fn($q) => $q->orWhereIn('contractor_id', $allContractors))
                ->when($assignedCompanies->isNotEmpty(), fn($q) => $q->orWhereIn('company_id', $assignedCompanies));
        });
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
            $this->filterDepartment = [];
        }
    }
    public function selectDepartment($id, $name)
    {
        $this->reset('searchContractor', 'contractor_id');
        $this->department_id = $id;
        $this->search = $name;
        $this->filterDepartment = $name;
        $this->showDropdown = false;
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
            $this->filterContractor = [];
        }
    }
    public function selectContractor($id, $name)
    {
        $this->reset('search', 'department_id');
        $this->contractor_id = $id;
        $this->searchContractor = $name;
        $this->filterContractor = $name;
        $this->showContractorDropdown = false;
    }

    public function updatedSearchPelapor()
    {
        if (strlen($this->searchPelapor) > 1) {
            $this->pelapors = User::where('name', 'like', '%' . $this->searchPelapor . '%')
                ->orderBy('name')
                ->limit(10)
                ->get();
            $this->showPelaporDropdown = true;
        } else {
            $this->pelapors = [];
            $this->showPelaporDropdown = false;
        }
    }
    public function selectPelapor($id, $name)
    {
        $this->pelapor_id = $id;
        $this->searchPelapor = $name;
        $this->showPelaporDropdown = false;
    }



    public function render()
    {
        $query = Hazard::with('pelapor')->withHazardCounts()->latest();
        $user = Auth::user(); // Ambil data user yang sedang login
        // ⚡️ IMPLEMENTASI FILTER CHECKBOX BARU
        $query->when($this->filterByAuth, function ($q) use ($user) {
            // Hanya tampilkan laporan di mana pelapor_id sama dengan ID pengguna yang sedang login
            $q->where('pelapor_id', $user->id);
        });


        // Terapkan scope untuk setiap filter
        $query->when($this->filterStatus !== 'all', function ($q) {
            $q->status($this->filterStatus);
        });

        $query->when($this->filterEventType, function ($q) {
            $q->byEventType($this->filterEventType);
        });

        $query->when($this->filterEventSubType, function ($q) {
            $q->byEventSubType($this->filterEventSubType);
        });

        $query->when($this->searchPelapor, function ($q) {
            $q->byPelapor($this->searchPelapor); // Meneruskan array langsung
        });
        $query->when(!empty($this->filterReporterDept), function ($q) {
            $q->whereHas('pelapor', function ($userQuery) {
                $userQuery->whereIn('department_name', $this->filterReporterDept);
            });
        });
        // Terapkan filter Department
        $query->byDepartments($this->filterDepartment);
        // Terapkan filter Contractor
        $query->byContractors($this->filterContractor);

        // ⚡️ Tambahkan filter rentang tanggal di sini
        $query->when($this->start_date && $this->end_date, function ($q) {
            $q->dateRange($this->start_date, $this->end_date);
        });
        $this->role = Auth::user()->role;

        if ($this->role === 'moderator') {
            $this->filterModeratorReports($query);
        }
        $today = Carbon::now()->format('Y-m-d : H:i');
        $reports = $query->orderByRaw('ABS(DATEDIFF(tanggal, ?)) ASC', [$today])->paginate(30);
        $availableStatuses = ['submitted', 'in_progress', 'pending', 'closed'];
        return view('livewire.hazard.hazard-report-panel', [
            'eventTypes' => EventType::where('event_type_name', 'like', '%' . 'hazard' . '%')->get(),
            'subTypes' => EventSubType::where('event_type_id', $this->filterEventType)->get(),

            'availableStatuses' => $availableStatuses,
            'reports' => $reports
        ]);
    }
    public function export()
    {
        $query = Hazard::with(['pelapor', 'eventType', 'eventSubType', 'department', 'contractor'])
            ->withHazardCounts()
            ->latest();

        $user = Auth::user();

        // Replikasi semua filter yang ada di render()
        $query->when($this->filterByAuth, function ($q) use ($user) {
            $q->where('pelapor_id', $user->id);
        });

        $query->when($this->filterStatus !== 'all' && !empty($this->filterStatus), function ($q) {
            $q->status($this->filterStatus);
        });

        $query->when($this->filterEventType, function ($q) {
            $q->byEventType($this->filterEventType);
        });

        $query->when($this->filterEventSubType, function ($q) {
            $q->byEventSubType($this->filterEventSubType);
        });

        $query->when($this->searchPelapor, function ($q) {
            $q->byPelapor($this->searchPelapor);
        });

        $query->when(!empty($this->filterReporterDept), function ($q) {
            $q->whereHas('pelapor', function ($userQuery) {
                $userQuery->whereIn('department_name', $this->filterReporterDept);
            });
        });

        $query->byDepartments($this->filterDepartment);
        $query->byContractors($this->filterContractor);

        $query->when($this->start_date && $this->end_date, function ($q) {
            $q->dateRange($this->start_date, $this->end_date);
        });

        if (Auth::user()->role === 'moderator') {
            $this->filterModeratorReports($query);
        }

        return Excel::download(new HazardExport($query), 'hazard-report-' . now()->format('Y-m-d') . '.xlsx');
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
