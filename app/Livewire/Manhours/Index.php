<?php

namespace App\Livewire\Manhours;

use Carbon\Carbon;
use App\Models\Manhour;
use Livewire\Component;
use App\Models\Custodian;
use App\Models\Contractor;
use App\Models\Department;
use App\Models\BusinessUnit;
use Livewire\WithPagination;
use App\Models\Department_group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class Index extends Component
{
    use WithPagination;
    public $custodian = [];
    public $deptGroup = [];
    public $companies = [];
    public $selectedId = null;
    public $confirmingDelete = false;
    public $canCreate = false;
    // input umum
    public $date;
    public $entityType;
    public $company;
    public $department;
    public $dept_group;
    public $form;
    public $search = '';
    public $range_date = '';
    public $start_date;
    public $end_date;
    public function updatedSearch($value)
    {

        $this->dispatch('manhoursSearchUpdated', search: $value);
    }
    public function updatingSearch()
    {
        $this->resetPage(); // Reset pagination ketika search berubah
    }
    public function updatingRangeDate()
    {
        $this->resetPage(); // Reset pagination ketika filter tanggal berubah
    }
    public function updatedRangeDate($value)
    {
        // Cek apakah nilai tidak kosong
        if (!empty($value)) {
            // Pisahkan string berdasarkan " ke "
            $dates = explode(' Ke ', $value);

            // Pastikan ada dua tanggal yang valid
            if (count($dates) === 2) {
                // Parse dan format tanggal awal ke YYYY/MM/DD
                $this->start_date = Carbon::parse(trim($dates[0]))->format('Y-m-d');

                // Parse dan format tanggal akhir ke YYYY/MM/DD
                $this->end_date = Carbon::parse(trim($dates[1]))->format('Y-m-d');
                $this->dispatch('dateRangeManhours', [
                    'start' => $this->start_date,
                    'end'   => $this->end_date,
                ]);
            }
        } else {
            $this->reset('start_date', 'end_date');
            $this->dispatch('dateRangeManhours', [
                'start' => null,
                'end'   => null,
            ]);
        }
    }
    public function clearFilter()
    {
        $this->reset('range_date', 'start_date', 'end_date');
    }


    public $hide = [
        'supervisor'     => false,
        'operational'    => false,
        'Administrator' => false,
    ];

    // jobclass setup
    public $jobclasses = [
        'supervisor'     => 'Supervisor',
        'operational'    => 'Operational',
        'Administrator' => 'Administrator',
    ];
    public $manhours = [
        'supervisor'     => null,
        'operational'    => null,
        'Administrator' => null,
    ];

    public $manpower = [
        'supervisor'     => null,
        'operational'    => null,
        'Administrator' => null,
    ];

    protected function rules()
    {
        $rules = [
            'date'        => 'required',
            'entityType' => 'required|string',
            'company'     => 'required|string',
            'department'  => 'required|string',
        ];

        foreach ($this->jobclasses as $key => $label) {
            // Jika $this->hide[$key] TRUE (dicentang), input dianggap nullable (diabaikan)
            $rules["manhours.$key"] = $this->hide[$key] ? 'required|numeric|min:0' : 'nullable';
            $rules["manpower.$key"] = $this->hide[$key] ? 'required|numeric|min:0' : 'nullable';
        }

        return $rules;
    }
    public function mount()
    {
        $this->handleCompanyList();
    }

    public function open_modal($id = null)
    {
        Gate::authorize('create', Manhour::class);

        $this->form = empty($id) ? 'Input' : 'Update';
        if ($id) {
            $this->selectedId = $id;
            $data = Manhour::findOrFail($id);

            $this->date        = Carbon::parse($data->date)->format('M-Y');
            $this->entityType = strtolower($data->company_category) === "contractor" ? "contractor" : "owner";
            $this->company     = $data->company;
            $this->department  = $data->department;
            $this->dept_group  = $data->dept_group;
            $this->updatedCompany();
            $this->updatedDepartment();
            // reset dulu
            foreach ($this->jobclasses as $key => $label) {
                $this->hide[$key]     = false;
                $this->manhours[$key] = null;
                $this->manpower[$key] = null;
            }

            // isi sesuai data yg ada di DB
            $manhoursData = Manhour::where('date', $data->date)
                ->where('company', $data->company)
                ->where('department', $data->department)
                ->where('dept_group', $data->dept_group)
                ->get();

            foreach ($manhoursData as $row) {
                $key = array_search($row->job_class, $this->jobclasses);
                if ($key !== true) {
                    $this->hide[$key]     = true;
                    $this->manhours[$key] = $row->manhours;
                    $this->manpower[$key] = $row->manpower;
                }
            }
        }
    }
    public function close_modal()
    {
        // Tutup modal
        $this->reset('selectedId');
        $this->hide = [
            'supervisor'    => false,
            'operational'   => false,
            'Administrator' => false,
        ];
        // Reset semua input form ke default
        $this->reset([
            'date',
            'entityType',
            'company',
            'department',
            'dept_group',
            'manhours',
            'manpower',
            'hide',
        ]);

        // Kalau perlu reset array jobclass manual
        foreach ($this->jobclasses as $key => $label) {
            $this->hide[$key]     = false;
            $this->manhours[$key] = null;
            $this->manpower[$key] = null;
        }
    }
    public string $recipient = '';
    public function updatedCompany()
    {
        if ($this->entityType === "contractor") {
            $custodian = Contractor::where('contractor_name', 'LIKE', $this->company)->first()->id ?? null;
            $this->custodian = Custodian::where('contractor_id', $custodian)->get();
        } elseif ($this->entityType === "owner") {
            $this->deptGroup = Department_group::get();
        } else {
            $this->reset('department');
        }
    }

    public function updatedDepartment()
    {
        $dept_id = Department::where('department_name', 'LIKE', $this->department)->value('id') ?? null;
        $this->dept_group = Department_group::where('department_id', $dept_id)->first()->Group->group_name ?? null;
    }
    public function updatedEntityType($value)
    {
        $this->handleCompanyList($value);
        $this->company = ''; // reset pilihan company
    }

    private function handleCompanyList($value = null)
    {
        $user = Auth::user();

        if ($user->roles()->where('role_id', 1)->exists()) { // Admin
            if ($value === 'owner') {
                $this->companies = [
                    'owners' => BusinessUnit::all(),
                    'contractors' => collect([]),
                ];
            } elseif ($value === 'contractor') {
                $this->companies = [
                    'owners' => collect([]),
                    'contractors' => Contractor::all(),
                ];
            } else {
                // Jika tidak ada entity_type yang dipilih, tampilkan keduanya (opsional)
                $this->companies = [
                    'owners' => BusinessUnit::all(),
                    'contractors' => Contractor::all(),
                ];
            }
        } else { // Contractor User
            if ($value === 'contractor') {
                $this->companies = [
                    'owners' => collect([]),
                    'contractors' => $user->contractors,
                ];
            } else {
                // Jika user contractor mencoba memilih owner, kosongkan keduanya
                $this->companies = [
                    'owners' => collect([]),
                    'contractors' => collect([]),
                ];
            }
        }
    }



    public function render()
    {
        // authorize viewAny
        Gate::authorize('viewAny', Manhour::class);
        $user = auth()->user();

        if ($user->roles()->where('role_id', 1)->exists()) {
            $query = Manhour::query();
        } else {
            $contractorNames = $user->contractors()->pluck('contractor_name');
            $query = Manhour::whereIn('company', $contractorNames);
        }
        // --- Perubahan dimulai di sini ---
        // Panggil scope dateRange() pada query, dan lewati properti filter
        $query->dateRange($this->start_date, $this->end_date)->search($this->search);
        $query = $query
            // 1. Urutkan berdasarkan 'date' secara descending (terbaru ke terlama)
            ->orderBy('date', 'desc')
            // 2. Kemudian, urutkan berdasarkan 'company_category' secara ascending (A ke Z)
            ->orderBy('company_category', 'asc');

        // --- Perubahan berakhir di sini ---
        return view('livewire.manhours.index', [
            'bu'        => BusinessUnit::all(),
            'cont'      => Contractor::all(),
            'departemen' => Department::get(),
            'data_manhours'  => $query->paginate(30),
        ]);
    }

    private function saveManhours($mode = 'create', $id = null)
    {
        $this->validate();

        $company_category = $this->entityType === "contractor"
            ? 'Contractor'
            : 'PT. Archi Indonesia';

        $bulan = Carbon::parse($this->date)->startOfMonth();

        foreach ($this->jobclasses as $key => $label) {

            // 1. Cek apakah record untuk job_class ini sudah ada di database
            $existingRecord = Manhour::where('date', $bulan)
                ->where('company', $this->company)
                ->where('department', $this->department)
                ->where('dept_group', $this->dept_group)
                ->where('job_class', $label)
                ->first();
            // 2. Jika checkbox TIDAK dicentang ATAU input kosong -> Hapus jika ada
            if (empty($this->hide[$key]) || (empty($this->manhours[$key]) && empty($this->manpower[$key]))) {
                if ($existingRecord) {
                    $existingRecord->delete();
                }
                continue;
            }
            // 3. Gunakan updateOrCreate berdasarkan kriteria unik (Bukan cuma ID)
            Manhour::updateOrCreate(
                [
                    'date'       => $bulan,
                    'company'    => $this->company,
                    'department' => $this->department,
                    'dept_group' => $this->dept_group,
                    'job_class'  => $label,
                ],
                [
                    'company_category' => $company_category,
                    'manhours'         => $this->manhours[$key],
                    'manpower'         => $this->manpower[$key],
                ]
            );
        }
        $this->close_modal();
        $this->dispatch('alert', [
            'text'            => $mode === 'create' ? "Data berhasil di input!!!" : "Data berhasil diperbarui!!!",
            'duration'        => 5000,
            'destination'     => '/contact',
            'newWindow'       => true,
            'close'           => true,
            'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
        ]);
    }

    public function update($id)
    {
        $this->saveManhours('update', $id);
    }
    public function store()
    {
        $this->saveManhours('create');
    }

    // Saat tombol hapus ditekan
    public function showDelete($id)
    {
        $this->selectedId = $id;
        $this->confirmingDelete = true; // buka modal konfirmasi
    }

    // Proses hapus data
    public function delete()
    {
        if ($this->selectedId) {
            Manhour::findOrFail($this->selectedId)->delete();

            // reset
            $this->selectedId = null;
           $this->dispatch('close-delete-modal');

            // opsional: emit event untuk notifikasi / refresh tabel
            $this->dispatch(
                'alert',
                [
                    'text' => "Data berhasil di hapus!!!",
                    'duration' => 5000,
                    'destination' => '/contact',
                    'newWindow' => true,
                    'close' => true,
                    'backgroundColor' => "linear-gradient(to right, #ff3333, #ff6666)",
                ]
            );
        }
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
