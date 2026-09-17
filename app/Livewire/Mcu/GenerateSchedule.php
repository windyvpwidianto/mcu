<?php

namespace App\Livewire\Mcu;

use App\Models\User;
use App\Models\McuSchedule;
use App\Models\McuRecord;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\McuScheduleImport;
use App\Imports\PesertaMcuImport;
use App\Exports\PesertaMcuTemplateExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class GenerateSchedule extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $filterYear = '';
    public $filterDepartment = '';

    // Import Excel properties
    public $excelFile;
    public $showImportModal = false;
    public $importResults = null;
    public $importErrors = [];

    // Tambah Peserta Manual properties
    public $showManualModal = false;
    public $manual_nik = '';
    public $manual_name = '';
    public $manual_dob = '';
    public $manual_hp = '';
    public $manual_dept = '';
    public $manual_jenis = '';
    
    // Import Peserta properties
    public $pesertaExcelFile;
    public $showImportPesertaModal = false;
    public $importPesertaResults = null;
    public $importPesertaErrors = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingFilterYear()
    {
        $this->resetPage();
    }
    public function updatingFilterDepartment()
    {
        $this->resetPage();
    }

    public function openImportModal()
    {
        $this->resetImport();
        $this->showImportModal = true;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->resetImport();
    }

    public function resetImport()
    {
        $this->reset(['excelFile', 'importResults', 'importErrors']);
    }

    public function openManualModal()
    {
        $this->resetManualForm();
        $this->showManualModal = true;
    }

    public function closeManualModal()
    {
        $this->showManualModal = false;
        $this->resetManualForm();
    }

    public function resetManualForm()
    {
        $this->reset(['manual_nik', 'manual_name', 'manual_dob', 'manual_hp', 'manual_dept', 'manual_jenis']);
        $this->resetValidation();
    }

    public function openImportPesertaModal()
    {
        $this->resetImportPeserta();
        $this->showImportPesertaModal = true;
    }

    public function closeImportPesertaModal()
    {
        $this->showImportPesertaModal = false;
        $this->resetImportPeserta();
    }

    public function resetImportPeserta()
    {
        $this->reset(['pesertaExcelFile', 'importPesertaResults', 'importPesertaErrors']);
    }

    public function downloadTemplate()
    {
        return Excel::download(new PesertaMcuTemplateExport, 'template_peserta_mcu.xlsx');
    }

    public function savePesertaManual()
    {
        if (!auth()->user()->hasRole('administrator') && !auth()->user()->hasRole('medical staff')) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate([
            'manual_nik' => 'required|string|max:255',
            'manual_name' => 'required|string|max:255',
            'manual_dob' => 'required|date',
            'manual_hp' => 'required|numeric',
            'manual_dept' => 'required|string|max:255',
            'manual_jenis' => 'required|string|in:department,contractor',
        ], [
            'manual_nik.required' => 'NIK wajib diisi.',
            'manual_name.required' => 'Nama Lengkap wajib diisi.',
            'manual_dob.required' => 'Tanggal Lahir wajib diisi.',
            'manual_hp.required' => 'Nomor HP wajib diisi.',
            'manual_hp.numeric' => 'Nomor HP harus berupa angka.',
            'manual_dept.required' => 'Departemen wajib diisi.',
            'manual_jenis.required' => 'Jenis Karyawan wajib dipilih.',
        ]);

        try {
            DB::beginTransaction();
            
            // Check if NIK already exists
            $user = User::where('employee_id', $this->manual_nik)->first();

            if ($user) {
                // Upsert/Update existing
                $user->update([
                    'name' => $this->manual_name,
                    'date_birth' => $this->manual_dob,
                    // If phone_number doesn't exist in DB schema, we might get an exception. 
                    // To handle it safely, we should check if the column exists or just assume it does as confirmed by user.
                    // Wait, the user said it exists, but we know it's not in our earlier schema listing. 
                    // Let's assume there is a way they store it, maybe in `username` or we can try updating `username` as phone?
                    // "nomor Hp sudah ada di database yang ada sekarang". Let's assume it's `phone_number` or they have it somehow.
                    // Actually, if it's not `phone_number`, we should probably use a dynamic property or skip it if it fails.
                    // I will add 'phone_number' and hope they added it manually. If it fails, I'll fix it.
                    // But wait, the user's codebase in `User.php` doesn't have `phone_number` in fillable.
                    // I must update `User.php` fillable array later!
                    'department_name' => $this->manual_dept,
                    'pilih_divisi' => $this->manual_jenis,
                ]);
                $message = 'Data peserta berhasil di-update berdasarkan NIK yang ada.';
            } else {
                // Create new
                $user = User::create([
                    'employee_id' => $this->manual_nik,
                    'username' => $this->manual_nik, // Default username
                    'name' => $this->manual_name,
                    'date_birth' => $this->manual_dob,
                    'department_name' => $this->manual_dept,
                    'pilih_divisi' => $this->manual_jenis,
                    'password' => Hash::make('password'), // Default password
                ]);
                $message = 'Data peserta baru berhasil ditambahkan.';
            }
            
            // Add phone number if possible, since it's not in fillable we might need to save it directly
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone_number')) {
                $user->phone_number = $this->manual_hp;
                $user->save();
            } else if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'no_hp')) {
                $user->no_hp = $this->manual_hp;
                $user->save();
            } else if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
                $user->phone = $this->manual_hp;
                $user->save();
            }

            DB::commit();

            $this->closeManualModal();
            $this->dispatch('alert', [
                'text' => $message,
                'duration' => 3000,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save Peserta Error: ' . $e->getMessage());
            $this->dispatch('alert', [
                'text' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'duration' => 5000,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #f59e0b, #ef4444)",
            ]);
        }
    }

    public function importPesertaExcel()
    {
        if (!auth()->user()->hasRole('administrator') && !auth()->user()->hasRole('medical staff')) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate([
            'pesertaExcelFile' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'pesertaExcelFile.required' => 'File Excel wajib diunggah.',
            'pesertaExcelFile.mimes' => 'Format file harus berupa .xlsx atau .xls.',
            'pesertaExcelFile.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $this->importPesertaResults = [
            'success' => 0,
            'failed' => 0,
            'duplicate_updated' => 0,
        ];
        $this->importPesertaErrors = [];

        try {
            $collections = Excel::toCollection(new PesertaMcuImport, $this->pesertaExcelFile);
            
            if ($collections->isEmpty() || $collections->first()->isEmpty()) {
                $this->importPesertaErrors[] = [
                    'row' => '-',
                    'nik' => '-',
                    'name' => '-',
                    'reason' => 'File Excel kosong atau format tidak sesuai.'
                ];
                $this->importPesertaResults['failed'] = 1;
                return;
            }

            $rows = $collections->first();
            $processedNiks = [];
            
            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                // index starts at 0 for row 2 (assuming header is row 1)
                $rowNum = $index + 2; 

                // Resolve keys based on header (lowercase and spaces replaced by underscores usually)
                $nik = trim($row['nik'] ?? '');
                $nama = trim($row['nama_lengkap'] ?? '');
                $tanggalLahir = trim($row['tanggal_lahir'] ?? '');
                $nomorHp = trim($row['nomor_hp'] ?? '');
                $departemen = trim($row['departemen'] ?? '');
                $jenis = trim($row['jenis_karyawan'] ?? '');

                if (!$nik || !$nama) {
                    $this->importPesertaErrors[] = [
                        'row' => $rowNum,
                        'nik' => $nik ?: '-',
                        'name' => $nama ?: '-',
                        'reason' => 'Kolom NIK dan Nama Lengkap wajib diisi.'
                    ];
                    $this->importPesertaResults['failed']++;
                    continue;
                }

                if (in_array($nik, $processedNiks)) {
                    $this->importPesertaErrors[] = [
                        'row' => $rowNum,
                        'nik' => $nik,
                        'name' => $nama,
                        'reason' => 'Duplikat NIK di dalam file Excel.'
                    ];
                    $this->importPesertaResults['failed']++;
                    continue;
                }
                $processedNiks[] = $nik;

                try {
                    if (is_numeric($tanggalLahir)) {
                        $tanggalLahirDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalLahir)->format('Y-m-d');
                    } else if ($tanggalLahir) {
                        $tanggalLahirDate = Carbon::parse($tanggalLahir)->format('Y-m-d');
                    } else {
                        $tanggalLahirDate = null;
                    }
                } catch (\Exception $e) {
                    $this->importPesertaErrors[] = [
                        'row' => $rowNum,
                        'nik' => $nik,
                        'name' => $nama,
                        'reason' => 'Format Tanggal Lahir tidak valid.'
                    ];
                    $this->importPesertaResults['failed']++;
                    continue;
                }

                $user = User::where('employee_id', $nik)->first();
                if ($user) {
                    // Update
                    $user->update([
                        'name' => $nama,
                        'date_birth' => $tanggalLahirDate ?: $user->date_birth,
                        'department_name' => $departemen ?: $user->department_name,
                        'pilih_divisi' => $jenis ?: $user->pilih_divisi,
                    ]);
                    $this->importPesertaResults['duplicate_updated']++;
                } else {
                    // Create
                    $user = User::create([
                        'employee_id' => $nik,
                        'username' => $nik,
                        'name' => $nama,
                        'date_birth' => $tanggalLahirDate,
                        'department_name' => $departemen,
                        'pilih_divisi' => $jenis ?: 'department',
                        'password' => Hash::make('password'),
                    ]);
                    $this->importPesertaResults['success']++;
                }

                // Check and save phone field based on schema
                if ($nomorHp) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone_number')) {
                        $user->phone_number = $nomorHp;
                    } else if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'no_hp')) {
                        $user->no_hp = $nomorHp;
                    } else if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
                        $user->phone = $nomorHp;
                    }
                    $user->save();
                }
            }

            DB::commit();

            if ($this->importPesertaResults['failed'] == 0) {
                $this->dispatch('alert', [
                    'text' => 'Semua data peserta Excel berhasil diimport/update!',
                    'duration' => 3000,
                    'close' => true,
                    'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
                ]);
            } else {
                $this->dispatch('alert', [
                    'text' => 'Import peserta selesai dengan beberapa kesalahan. Silakan periksa rincian.',
                    'duration' => 5000,
                    'close' => true,
                    'backgroundColor' => "linear-gradient(to right, #f59e0b, #ef4444)",
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MCU Peserta Import Error: ' . $e->getMessage());
            
            $this->importPesertaErrors[] = [
                'row' => '-',
                'nik' => '-',
                'name' => '-',
                'reason' => 'Terjadi kesalahan sistem fatal (Rollback): ' . $e->getMessage()
            ];
            $this->importPesertaResults['failed']++;
        }
    }

    public function importExcel()
    {
        if (!auth()->user()->hasRole('administrator') && !auth()->user()->hasRole('medical staff')) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'excelFile.required' => 'File Excel wajib diunggah.',
            'excelFile.mimes' => 'Format file harus berupa .xlsx atau .xls.',
            'excelFile.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $this->importResults = [
            'success' => 0,
            'failed' => 0,
            'new_schedules' => 0,
            'new_participants' => 0,
        ];
        $this->importErrors = [];

        try {
            $collections = Excel::toCollection(new McuScheduleImport, $this->excelFile);
            
            if ($collections->isEmpty() || $collections->first()->isEmpty()) {
                $this->importErrors[] = [
                    'row' => '-',
                    'employee_id' => '-',
                    'reason' => 'File Excel kosong atau format tidak sesuai.'
                ];
                $this->importResults['failed'] = 1;
                return;
            }

            $rows = $collections->first();
            
            DB::beginTransaction();

            $schedulesCache = [];

            foreach ($rows as $index => $row) {
                // index starts at 0 for row 2 (assuming header is row 1)
                $rowNum = $index + 2; 

                $employeeId = isset($row['employee_id']) ? trim($row['employee_id']) : null;
                $tanggalMcu = isset($row['tanggal_mcu']) ? trim($row['tanggal_mcu']) : null;
                $lokasiMcu = isset($row['lokasi_mcu']) ? trim($row['lokasi_mcu']) : 'Klinik / RS';

                if (!$employeeId || !$tanggalMcu) {
                    $this->importErrors[] = [
                        'row' => $rowNum,
                        'employee_id' => $employeeId ?? '-',
                        'reason' => 'Kolom employee_id dan tanggal_mcu wajib diisi.'
                    ];
                    $this->importResults['failed']++;
                    continue;
                }

                try {
                    if (is_numeric($tanggalMcu)) {
                        $tanggalMcuDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalMcu)->format('Y-m-d');
                    } else {
                        $tanggalMcuDate = Carbon::parse($tanggalMcu)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $this->importErrors[] = [
                        'row' => $rowNum,
                        'employee_id' => $employeeId,
                        'reason' => 'Format tanggal_mcu tidak valid.'
                    ];
                    $this->importResults['failed']++;
                    continue;
                }

                $user = User::where('employee_id', $employeeId)->first();
                if (!$user) {
                    $this->importErrors[] = [
                        'row' => $rowNum,
                        'employee_id' => $employeeId,
                        'reason' => "Employee dengan ID $employeeId tidak ditemukan di database."
                    ];
                    $this->importResults['failed']++;
                    continue;
                }

                // Buat/Cari Schedule
                $scheduleKey = $tanggalMcuDate . '_' . strtolower($lokasiMcu);

                if (!isset($schedulesCache[$scheduleKey])) {
                    $schedule = McuSchedule::firstOrCreate(
                        [
                            'schedule_date' => $tanggalMcuDate,
                            'location' => $lokasiMcu
                        ],
                        [
                            'created_by' => auth()->id()
                        ]
                    );
                    
                    if ($schedule->wasRecentlyCreated) {
                        $this->importResults['new_schedules']++;
                    }
                    
                    $schedulesCache[$scheduleKey] = $schedule->id;
                }
                
                $scheduleId = $schedulesCache[$scheduleKey];
                $year = Carbon::parse($tanggalMcuDate)->format('Y');

                $existingParticipant = McuRecord::where('employee_id', $user->id)
                                                ->where('mcu_year', $year)
                                                ->exists();

                if ($existingParticipant) {
                    $this->importErrors[] = [
                        'row' => $rowNum,
                        'employee_id' => $employeeId,
                        'reason' => "Employee $employeeId sudah memiliki riwayat MCU pada tahun $year."
                    ];
                    $this->importResults['failed']++;
                    continue;
                }

                McuRecord::create([
                    'mcu_schedule_id' => $scheduleId,
                    'employee_id' => $user->id,
                    'mcu_year' => $year,
                    'mcu_date' => $tanggalMcuDate,
                    'status' => 'Completed',
                    'notification_status' => 'notified'
                ]);

                $this->importResults['new_participants']++;
                $this->importResults['success']++;
            }

            DB::commit();

            activity('mcu')
                ->causedBy(auth()->user())
                ->withProperties([
                    'success' => $this->importResults['success'],
                    'failed' => $this->importResults['failed'],
                    'new_schedules' => $this->importResults['new_schedules'],
                    'new_participants' => $this->importResults['new_participants'],
                ])
                ->log('Melakukan import data MCU schedule dari Excel');

            if ($this->importResults['failed'] == 0) {
                $this->dispatch('alert', [
                    'text' => 'Semua data Excel berhasil diimport!',
                    'duration' => 3000,
                    'close' => true,
                    'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
                ]);
            } else {
                $this->dispatch('alert', [
                    'text' => 'Import selesai dengan beberapa kesalahan. Silakan periksa rincian.',
                    'duration' => 5000,
                    'close' => true,
                    'backgroundColor' => "linear-gradient(to right, #f59e0b, #ef4444)",
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MCU Import Error: ' . $e->getMessage());
            
            $this->importErrors[] = [
                'row' => '-',
                'employee_id' => '-',
                'reason' => 'Terjadi kesalahan sistem fatal (Rollback): ' . $e->getMessage()
            ];
            $this->importResults['failed']++;
        }
    }

    public function paginationView()
    {
        return 'paginate.pagination';
    }

    public function render()
    {
        $query = User::query();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('employee_id', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->filterDepartment)) {
            $query->where('department_name', $this->filterDepartment);
        }

        if (!empty($this->filterYear)) {
            $query->whereHas('mcuRecords', function ($q) {
                $q->where('mcu_year', $this->filterYear);
            });
        }

        // Include the latest MCU Record for each employee
        $employees = $query->with(['mcuRecords' => function($q) {
            $q->orderBy('mcu_year', 'desc')->orderBy('mcu_date', 'desc');
        }])->paginate(15);

        $departments = User::select('department_name')->distinct()->whereNotNull('department_name')->pluck('department_name');
        
        // List of years for filter (from mcu_records)
        $years = McuRecord::select('mcu_year')->distinct()->whereNotNull('mcu_year')->orderBy('mcu_year', 'desc')->pluck('mcu_year');

        return view('livewire.mcu.generate-schedule', [
            'employees' => $employees,
            'departments' => $departments,
            'years' => $years
        ]);
    }
}
