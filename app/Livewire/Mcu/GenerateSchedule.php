<?php

namespace App\Livewire\Mcu;

use App\Models\User;
use App\Models\McuSchedule;
use App\Models\McuRecord;
use App\Models\McuResult;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\McuScheduleImport;
use App\Imports\PesertaMcuImport;
use App\Imports\McuHistoryImport;
use App\Exports\PesertaMcuTemplateExport;
use App\Exports\McuHistoryTemplateExport;
use App\Models\Role;
use App\Models\Department;
use App\Models\Contractor;
use Illuminate\Validation\Rule;
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

    // Manual Add Peserta properties
    public $showManualModal = false;
    public $manual_nik = '';
    public $manual_badge = '';
    public $manual_name = '';
    public $manual_dob = '';
    public $manual_hp = '';
    public $manual_gender = '';
    public $manual_username = '';
    public $manual_email = '';
    public $manual_date_commenced = '';
    public $manual_role_id = '';
    public $manual_password = '';
    public $manual_password_confirmation = '';
    public $manual_deptCont = 'department'; // PT. MSM & PT. TTN or Kontraktor
    public $manual_dep_cont_name = ''; // Stores actual department or company name
    public $department_id;
    public $contractor_id;
    
    // For custom dropdown search
    public $searchDept = '';
    public $searchContractor = '';
    public $showDropdown = false;
    public $showContractorDropdown = false;
    public $searchDepartments = [];
    public $searchContractors = [];
    
    // Import Peserta properties
    public $pesertaExcelFile;
    public $showImportPesertaModal = false;
    public $importPesertaResults = null;
    public $importPesertaErrors = [];

    // Import MCU History properties
    public $historyExcelFile;
    public $showImportHistoryModal = false;
    public $importHistoryResults = null;
    public $importHistoryErrors = [];
    public $historyYears = [2023, 2024, 2025, 2026];

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
        $this->reset([
            'manual_nik', 'manual_badge', 'manual_name', 'manual_dob', 'manual_hp', 
            'manual_gender', 'manual_username', 'manual_email', 'manual_date_commenced', 
            'manual_role_id', 'manual_password', 'manual_password_confirmation',
            'manual_deptCont', 'manual_dep_cont_name', 'department_id', 'contractor_id',
            'searchDept', 'searchContractor', 'showDropdown', 'showContractorDropdown'
        ]);
        $this->resetValidation();
        $this->dispatch('dateLoaded');
    }

    public function updatedSearchDept()
    {
        if (strlen($this->searchDept) > 1) {
            $this->searchDepartments = Department::where('department_name', 'like', '%' . $this->searchDept . '%')
                ->orderBy('department_name')
                ->limit(10)
                ->get();
            $this->showDropdown = true;
        } else {
            $this->searchDepartments = [];
            $this->showDropdown = false;
        }
    }

    public function selectDepartment($id, $name)
    {
        $this->reset('searchContractor', 'contractor_id');
        $this->department_id = $id;
        $this->searchDept = $name;
        $this->manual_dep_cont_name = $name;
        $this->showDropdown = false;
        $this->validateOnly('department_id');
    }

    public function updatedSearchContractor()
    {
        if (strlen($this->searchContractor) > 1) {
            $this->searchContractors = Contractor::query()
                ->where('contractor_name', 'like', '%' . $this->searchContractor . '%')
                ->orderBy('contractor_name')
                ->limit(10)
                ->get();
            $this->showContractorDropdown = true;
        } else {
            $this->searchContractors = [];
            $this->showContractorDropdown = true;
        }
    }

    public function selectContractor($id, $name)
    {
        $this->reset('searchDept', 'department_id');
        $this->contractor_id = $id;
        $this->searchContractor = $name;
        $this->manual_dep_cont_name = $name;
        $this->showContractorDropdown = false;
        $this->validateOnly('contractor_id');
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

        // Require fields that should be standard
        $this->validate([
            'manual_name' => 'required|string|max:255',
            'manual_badge' => 'required|string|max:255', // Employee ID is required generally
            'manual_nik' => 'nullable|string|max:255',
            'manual_hp' => 'nullable|numeric',
            'manual_gender' => 'nullable|in:L,P',
            'manual_dob' => 'nullable|date',
            'manual_date_commenced' => 'nullable|date',
            'manual_role_id' => 'nullable',
            
            // Password confirmation logic
            'manual_password' => 'nullable|string|min:6',
            'manual_password_confirmation' => 'nullable|same:manual_password',
            
            'manual_username' => [
                'nullable',
                'string',
                'max:255',
            ],
            'manual_email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'department_id' => 'required_without:contractor_id',
            'contractor_id' => 'required_without:department_id',
        ], [
            'manual_name.required' => 'Nama Lengkap wajib diisi.',
            'manual_badge.required' => 'ID Badge / Employee ID wajib diisi.',
            'manual_hp.numeric' => 'Nomor HP harus berupa angka.',
            'manual_password_confirmation.same' => 'Konfirmasi password tidak cocok.',
            'department_id.required_without' => 'Departemen wajib dipilih jika kontraktor tidak diisi.',
            'contractor_id.required_without' => 'Kontraktor wajib dipilih jika departemen tidak diisi.',
            'manual_email.email' => 'Format email tidak valid.',
        ]);

        try {
            DB::beginTransaction();

            // Find user by Employee ID
            $user = User::where('employee_id', $this->manual_badge)->first();

            $userData = [
                'name' => $this->manual_name,
                'nik' => $this->manual_nik,
                'phone_number' => $this->manual_hp,
                'gender' => $this->manual_gender,
                'date_birth' => $this->manual_dob,
                'date_commenced' => $this->manual_date_commenced,
                'role_id' => $this->manual_role_id,
                'pilih_divisi' => $this->manual_deptCont,
            ];

            if ($this->manual_deptCont === 'department') {
                $userData['department_name'] = $this->manual_dep_cont_name;
                $userData['company_name'] = null;
            } else {
                $userData['department_name'] = null;
                $userData['company_name'] = $this->manual_dep_cont_name;
            }

            // Fallback for username if not filled (only on create or if explicitly missing)
            if ($this->manual_username) {
                $userData['username'] = $this->manual_username;
            } elseif (!$user) {
                $userData['username'] = $this->manual_badge;
            }

            if ($this->manual_email) {
                $userData['email'] = $this->manual_email;
            }

            if (!empty($this->manual_password)) {
                $userData['password'] = Hash::make($this->manual_password);
            } elseif (!$user) {
                $userData['password'] = Hash::make('password'); // Default password for new users if blank
            }

            if ($user) {
                // Update existing
                $user->update($userData);
                $message = 'Data peserta berhasil di-update berdasarkan ID Badge yang ada.';
            } else {
                // Create new
                $userData['employee_id'] = $this->manual_badge;
                $user = User::create($userData);
                $message = 'Data peserta baru berhasil ditambahkan.';
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

    public function openImportHistoryModal()
    {
        $this->reset(['historyExcelFile', 'importHistoryResults', 'importHistoryErrors']);
        $this->showImportHistoryModal = true;
    }

    public function closeImportHistoryModal()
    {
        $this->showImportHistoryModal = false;
        $this->reset(['historyExcelFile', 'importHistoryResults', 'importHistoryErrors']);
    }

    public function downloadHistoryTemplate()
    {
        return Excel::download(new McuHistoryTemplateExport, 'template_mcu_history.xlsx');
    }

    public function importHistory()
    {
        if (!auth()->user()->hasRole('administrator') && !auth()->user()->hasRole('medical staff')) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate([
            'historyExcelFile' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'historyExcelFile.required' => 'File Excel wajib diunggah.',
            'historyExcelFile.mimes'    => 'Format file harus berupa .xlsx atau .xls.',
            'historyExcelFile.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        // Extend execution time for large files
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        $this->importHistoryResults = [
            'processed'      => 0,
            'success'        => 0,
            'skipped'        => 0,
            'user_not_found' => 0,
        ];
        $this->importHistoryErrors = [];

        try {
            // Raw import — NO WithHeadingRow so year columns (2023, 2024...) are not mangled
            $collections = Excel::toCollection(new McuHistoryImport, $this->historyExcelFile);

            if ($collections->isEmpty() || $collections->first()->isEmpty()) {
                $this->importHistoryErrors[] = [
                    'row'    => '-',
                    'emp_id' => '-',
                    'reason' => 'File Excel kosong atau format tidak sesuai.',
                ];
                return;
            }

            $allRows = $collections->first();

            if ($allRows->count() < 2) {
                $this->importHistoryErrors[] = [
                    'row'    => '-',
                    'emp_id' => '-',
                    'reason' => 'File hanya memiliki baris header, tidak ada data.',
                ];
                return;
            }

            // First row is the header
            $headerRow  = $allRows->first()->values();
            $dataRows   = $allRows->slice(1); // skip header row

            // Build column index map from header
            // Expected: employee_id, full_name, 2023, 2024, 2025, 2026, ...
            $colMap = [];
            foreach ($headerRow as $colIndex => $colName) {
                $colMap[strtolower(trim((string)$colName))] = $colIndex;
            }

            // Detect year columns from header
            $yearColumns = [];
            foreach ($headerRow as $colIndex => $colName) {
                $name = trim((string)$colName);
                if (is_numeric($name) && (int)$name >= 2000 && (int)$name <= 2100) {
                    $yearColumns[(int)$name] = $colIndex;
                }
            }

            if (empty($yearColumns)) {
                $yearColumns = array_flip($this->historyYears); // fallback
            }

            // Pre-load all users into memory cache for speed
            $userCache = User::select('id', 'employee_id', 'name')
                ->get()
                ->keyBy('employee_id');

            DB::beginTransaction();

            foreach ($dataRows as $index => $rawRow) {
                $row    = $rawRow->values(); // convert to 0-indexed
                $rowNum = $index + 2;

                $empIdCol   = $colMap['employee_id'] ?? 0;
                $fullNameCol = $colMap['full_name'] ?? 1;

                $employeeId = isset($row[$empIdCol]) ? trim((string)$row[$empIdCol]) : null;
                $fullName   = isset($row[$fullNameCol]) ? trim((string)$row[$fullNameCol]) : null;

                if (!$employeeId) {
                    $this->importHistoryErrors[] = [
                        'row'    => $rowNum,
                        'emp_id' => '-',
                        'reason' => 'employee_id kosong.',
                    ];
                    continue;
                }

                // Look up user from in-memory cache
                $user = $userCache->get($employeeId);

                if (!$user) {
                    if ($fullName) {
                        $user = User::create([
                            'employee_id'  => $employeeId,
                            'username'     => $employeeId,
                            'name'         => $fullName,
                            'password'     => Hash::make('password'),
                            'pilih_divisi' => 'department',
                        ]);
                        $userCache->put($employeeId, $user);
                    } else {
                        $this->importHistoryErrors[] = [
                            'row'    => $rowNum,
                            'emp_id' => $employeeId,
                            'reason' => "Employee ID '$employeeId' tidak ditemukan di database.",
                        ];
                        $this->importHistoryResults['user_not_found']++;
                        continue;
                    }
                }

                // Update name if different
                if ($fullName && $user->name !== $fullName) {
                    $user->update(['name' => $fullName]);
                }

                $this->importHistoryResults['processed']++;

                foreach ($yearColumns as $year => $colIndex) {
                    $rawDate = isset($row[$colIndex]) ? $row[$colIndex] : null;

                    // Skip empty
                    if ($rawDate === null || $rawDate === '') {
                        continue;
                    }

                    // Parse date — use PhpSpreadsheet for Excel serial numbers (fast)
                    $mcuDate = null;
                    try {
                        $rawDate = trim((string)$rawDate);
                        if (is_numeric($rawDate)) {
                            // Excel serial date → PHP DateTime via PhpSpreadsheet
                            $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$rawDate);
                            $mcuDate = $dateObj->format('Y-m-d');
                        } else {
                            // String date — try common formats
                            $mcuDate = date('Y-m-d', strtotime($rawDate));
                            if (!$mcuDate || $mcuDate === '1970-01-01') {
                                throw new \Exception("Cannot parse date: $rawDate");
                            }
                        }
                    } catch (\Exception $ex) {
                        $this->importHistoryErrors[] = [
                            'row'    => $rowNum,
                            'emp_id' => $employeeId,
                            'reason' => "Tahun $year: Format tanggal '$rawDate' tidak valid.",
                        ];
                        $this->importHistoryResults['skipped']++;
                        continue;
                    }

                    // Check if record already exists for this year
                    $existing = McuRecord::where('employee_id', $user->id)
                        ->where('mcu_year', $year)
                        ->first();

                    if ($existing) {
                        $existing->update([
                            'mcu_date'            => $mcuDate,
                            'attendance_status'   => 'present',
                            'process_status'      => 'completed',
                            'notification_status' => 'notified',
                        ]);

                        McuResult::updateOrCreate(
                            ['mcu_record_id' => $existing->id],
                            [
                                'status'          => 'fit_to_work',
                                'workflow_status' => 'reviewed',
                                'is_published'    => true,
                            ]
                        );

                        $this->importHistoryResults['skipped']++;
                    } else {
                        $newRecord = McuRecord::create([
                            'mcu_schedule_id'     => null,
                            'employee_id'         => $user->id,
                            'mcu_year'            => $year,
                            'mcu_date'            => $mcuDate,
                            'attendance_status'   => 'present',
                            'process_status'      => 'completed',
                            'notification_status' => 'notified',
                        ]);

                        McuResult::create([
                            'mcu_record_id'   => $newRecord->id,
                            'status'          => 'fit_to_work',
                            'workflow_status' => 'reviewed',
                            'is_published'    => true,
                        ]);

                        $this->importHistoryResults['success']++;
                    }
                }
            }

            DB::commit();

            $this->dispatch('alert', [
                'text'            => "Import riwayat MCU selesai! {$this->importHistoryResults['success']} record baru, {$this->importHistoryResults['skipped']} diupdate/dilewati.",
                'duration'        => 5000,
                'close'           => true,
                'backgroundColor' => 'linear-gradient(to right, #06b6d4, #22c55e)',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('MCU History Import Error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            $this->importHistoryErrors[] = [
                'row'    => '-',
                'emp_id' => '-',
                'reason' => 'Kesalahan sistem: ' . $e->getMessage(),
            ];
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
        
        $allDepartments = \App\Models\Department::pluck('department_name');
        
        // List of years for filter (from mcu_records)
        $years = McuRecord::select('mcu_year')->distinct()->whereNotNull('mcu_year')->orderBy('mcu_year', 'desc')->pluck('mcu_year');

        $roles = Role::all();

        return view('livewire.mcu.generate-schedule', [
            'employees' => $employees,
            'departments' => $departments,
            'allDepartments' => $allDepartments,
            'years' => $years,
            'roles' => $roles
        ]);
    }
}
