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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
