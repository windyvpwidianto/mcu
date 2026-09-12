<?php

namespace App\Livewire\Mcu;

use App\Models\McuSchedule;
use App\Models\User;
use App\Notifications\McuReminderNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\McuScheduleImport;
use App\Models\McuParticipant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateSchedule extends Component
{
    public $schedule_date;
    public $search = '';
    public $location;
    public $searchSupervisor = [];
    public $showSupervisorDropdown = [];
    public $manualSupervisorMode = [];
    public $manualSupervisorName = [];

    // --- Variabel Dept Head ---
    public $searchDeptHead = [];
    public $showDeptHeadDropdown = [];
    public $manualDeptHeadMode = [];
    public $manualDeptHeadName = [];

    public $activeSpvRowId = null;
    public $activeDeptRowId = null;
    public $participantsData = [];

    // Import Excel properties
    public $excelFile;
    public $showImportModal = false;
    public $importResults = null;
    public $importErrors = [];

    use WithPagination, WithFileUploads;

    public function rules()
    {
        return [
            'schedule_date' => 'required|date|after:today',
            'location'      => 'required|string',

            'participantsData' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    // Mengecek apakah ada setidaknya satu yang selected = true
                    $hasSelected = collect($value)->where('selected', true)->count() > 0;
                    if (!$hasSelected) {
                        $fail('Pilih minimal 1 peserta.');
                    }
                },
            ],

            // Perbaikan: tambahkan digits_between di sini agar sesuai dengan pesan error Anda
            'participantsData.*.selected' => 'required|boolean',
            'participantsData.*.wa'       => 'required_if:participantsData.*.selected,true|numeric|digits_between:9,15',

            // Perbaikan: tambahkan required_if agar validasi hanya berjalan untuk yang terpilih saja
            'participantsData.*.spv_id'   => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'schedule_date.required' => 'Tanggal wajib diisi.',
            'schedule_date.date'     => 'Format tanggal harus berupa tanggal.',
            'schedule_date.after'    => 'Tanggal harus lebih besar dari tanggal hari ini.',
            'location.required'      => 'Lokasi wajib diisi.',
            'location.string'        => 'Format lokasi harus berupa teks.',

            'participantsData.required' => 'Minimal satu peserta wajib dipilih.',

            // Pesan untuk WA
            'participantsData.*.wa.required_if'      => 'Nomor WhatsApp wajib diisi untuk peserta yang dipilih.',
            'participantsData.*.wa.numeric'          => 'Format nomor WhatsApp harus berupa angka.',
            'participantsData.*.wa.digits_between'   => 'Nomor WhatsApp harus antara 9-15 digit.',

            // Tambahkan pesan untuk Supervisor jika perlu
            'participantsData.*.spv_id.required_if'          => 'Supervisor wajib dipilih atau diisi manual.',
            'participantsData.*.spv_name_manual.required_if' => 'Supervisor wajib dipilih atau diisi manual.',
        ];
    }
    public function updated($propertyName)
    {
        // Ini akan memvalidasi field yang sedang diubah saja secara realtime
        $this->validateOnly($propertyName);
    }

    // --- SUPERVISOR MANUAL ---
    public function enableManualSupervisor($employeeId)
    {
        $this->manualSupervisorMode[$employeeId] = true;
    }

    public function addSupervisorManual($employeeId)
    {
        if (isset($this->manualSupervisorName[$employeeId])) {
            $manualName = $this->manualSupervisorName[$employeeId];

            $this->participantsData[$employeeId]['spv_id'] = null;
            $this->participantsData[$employeeId]['spv_name_manual'] = $manualName;
            $this->searchSupervisor[$employeeId] = $manualName;
            $this->manualSupervisorMode[$employeeId] = false;
        }
    }

    // --- PILIH SUPERVISOR (ANTI RACE-CONDITION) ---
    public function selectSupervisor($managerId, $managerName)
    {
        $employeeId = $this->activeSpvRowId;

        // Fallback jika terjadi delay koneksi Livewire
        if (!$employeeId) {
            $openedDropdowns = array_filter($this->showSupervisorDropdown);
            $employeeId = !empty($openedDropdowns) ? key($openedDropdowns) : null;
        }

        if ($employeeId) {
            $this->participantsData[$employeeId]['spv_id'] = $managerId;
            $this->searchSupervisor[$employeeId] = $managerName;
            $this->manualSupervisorMode[$employeeId] = false;
            $this->showSupervisorDropdown[$employeeId] = false;
        }

        $this->activeSpvRowId = null; // Reset setelah dipilih
    }

    // --- DEPT HEAD MANUAL ---
    public function enableManualDeptHead($employeeId)
    {
        $this->manualDeptHeadMode[$employeeId] = true;
    }

    public function addDeptHeadManual($employeeId)
    {
        if (isset($this->manualDeptHeadName[$employeeId])) {
            $manualName = $this->manualDeptHeadName[$employeeId];

            $this->participantsData[$employeeId]['dept_head_id'] = null;
            $this->participantsData[$employeeId]['dept_head_name_manual'] = $manualName;
            $this->searchDeptHead[$employeeId] = $manualName;
            $this->manualDeptHeadMode[$employeeId] = false;
        }
    }

    // --- PILIH DEPT HEAD (ANTI RACE-CONDITION) ---
    public function selectDeptHead($managerId, $managerName)
    {
        $employeeId = $this->activeDeptRowId;

        // Fallback jika terjadi delay koneksi Livewire
        if (!$employeeId) {
            $openedDropdowns = array_filter($this->showDeptHeadDropdown);
            $employeeId = !empty($openedDropdowns) ? key($openedDropdowns) : null;
        }

        if ($employeeId) {
            $this->participantsData[$employeeId]['dept_head_id'] = $managerId;
            $this->searchDeptHead[$employeeId] = $managerName;
            $this->manualDeptHeadMode[$employeeId] = false;
            $this->showDeptHeadDropdown[$employeeId] = false;
        }

        $this->activeDeptRowId = null; // Reset setelah dipilih
    }

    public function updatedSearchDeptHead($value, $key)
    {
        $this->showDeptHeadDropdown[$key] = true;
        $this->activeDeptRowId = $key;
    }

    public function updatedSearchSupervisor($value, $key)
    {
        $this->showSupervisorDropdown[$key] = true;
        $this->activeSpvRowId = $key;
    }

    // --- GENERATE JADWAL ---
    public function generateJadwal()
    {
        // 1. Cek apakah ada peserta yang dipilih

        $this->validate();

        $selectedParticipants = array_filter($this->participantsData, function ($data) {
            return isset($data['selected']) && $data['selected'] == true;
        });

        if (empty($selectedParticipants)) {
            session()->flash('error', 'Pilih minimal 1 peserta MCU.');
            return;
        }


        $schedule = McuSchedule::create([
            'schedule_date' => $this->schedule_date,
            'location' => $this->location,
            'created_by' => auth()->id(),
        ]);

        foreach ($selectedParticipants as $employee_id => $data) {
            $participant = $schedule->participants()->create([
                'employee_id' => $employee_id,
                'whatsapp_number' => $data['wa'] ?? null,
                'supervisor_id' => $data['spv_id'] ?? null,
                'spv_wa_number' => $data['wa_spv'] ?? null,
                'dept_head_id' => $data['dept_head_id'] ?? null,
                'spv_name_manual' => $data['spv_name_manual'] ?? null,
                'dept_head_name_manual' => $data['dept_head_name_manual'] ?? null,
                'notification_status' => 'notified'
            ]);

            // 1. KIRIM KE KARYAWAN (Cukup cek apakah user ada, jangan hadang pakai nomor WA)
            $employee = User::find($employee_id);
            if ($employee) {
                // Method via() di notifikasi akan otomatis memilih kirim Email, WA, atau keduanya
                // tergantung data apa yang tersedia pada user/participant.
                defer(function () use ($employee, $participant) {
                    $employee->notifyNow(new McuReminderNotification($participant, 'new_schedule', [\App\Channels\WhatsAppChannel::class]));
                    try {
                        $employee->notify(new McuReminderNotification($participant, 'new_schedule', ['mail']));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('MCU Reminder Email Error (Employee): ' . $e->getMessage());
                    }
                });
            }

            // 2. KIRIM KE SUPERVISOR
            if ($participant->supervisor_id) {
                $spv = User::find($participant->supervisor_id);
                if ($spv) {
                    // Jika SPV terdaftar di sistem, kirim (email & WA akan diatur otomatis oleh via())
                    defer(function () use ($spv, $participant) {
                        $spv->notifyNow(new McuReminderNotification($participant, 'new_schedule_spv', [\App\Channels\WhatsAppChannel::class]));
                        try {
                            $spv->notify(new McuReminderNotification($participant, 'new_schedule_spv', ['mail']));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('MCU Reminder Email Error (SPV): ' . $e->getMessage());
                        }
                    });
                }
            } elseif (!empty($participant->spv_wa_number)) {
                // Jika SPV manual (tidak punya akun di sistem), kirim via On-Demand Notification (Khusus WA)
                defer(fn() => Notification::route('whatsapp', $participant->spv_wa_number)
                    ->notify(new McuReminderNotification($participant, 'new_schedule_spv')));
            }

            // 3. KIRIM KE DEPT HEAD (Khusus Dept Head, method via() Anda sudah mengunci hanya via Email)
            if ($participant->dept_head_id) {
                $deptHead = User::find($participant->dept_head_id);
                if ($deptHead && !empty($deptHead->email)) {
                    defer(function () use ($deptHead, $participant) {
                        try {
                            $deptHead->notify(new McuReminderNotification($participant, 'new_schedule_dept_head', ['mail']));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('MCU Reminder Email Error (Dept Head): ' . $e->getMessage());
                        }
                    });
                }
            }
        }

        $this->reset(['schedule_date', 'location', 'participantsData']);
        $this->dispatch('alert', [
            'text'            => "Jadwal MCU berhasil dibuat dan notifikasi sedang dikirim di latar belakang.",
            'duration'        => 5000,
            'close'           => true,
            'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
        ]);
    }

    public function updatingSearch()
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
                $lokasiMcu = isset($row['lokasi_mcu']) ? trim($row['lokasi_mcu']) : null;

                if (!$employeeId || !$tanggalMcu || !$lokasiMcu) {
                    $this->importErrors[] = [
                        'row' => $rowNum,
                        'employee_id' => $employeeId ?? '-',
                        'reason' => 'Kolom employee_id, tanggal_mcu, dan lokasi_mcu wajib diisi.'
                    ];
                    $this->importResults['failed']++;
                    continue;
                }

                try {
                    if (is_numeric($tanggalMcu)) {
                        $tanggalMcuDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalMcu)->format('Y-m-d');
                    } else {
                        $tanggalMcuDate = \Carbon\Carbon::parse($tanggalMcu)->format('Y-m-d');
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

                $existingParticipant = McuParticipant::where('mcu_schedule_id', $scheduleId)
                                                     ->where('employee_id', $user->id)
                                                     ->exists();

                if ($existingParticipant) {
                    $this->importErrors[] = [
                        'row' => $rowNum,
                        'employee_id' => $employeeId,
                        'reason' => "Employee $employeeId sudah terdaftar pada jadwal $tanggalMcuDate di lokasi $lokasiMcu."
                    ];
                    $this->importResults['failed']++;
                    continue;
                }

                McuParticipant::create([
                    'mcu_schedule_id' => $scheduleId,
                    'employee_id' => $user->id,
                    'supervisor_id' => null,
                    'notification_status' => 'pending'
                ]);

                $this->importResults['new_participants']++;
                $this->importResults['success']++;
            }

            DB::commit();

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

    public function getEmployeeName($id)
    {
        // Cari nama karyawan berdasarkan ID
        // Jika data ada di memori, ambil dari sana. Jika tidak, query ke DB.
        $employee = User::find($id);
        return $employee ? $employee->name : 'Unknown';
    }

    public function removeParticipant($id)
    {
        // Mengubah status selected menjadi false untuk ID terkait
        if (isset($this->participantsData[$id])) {
            $this->participantsData[$id]['selected'] = false;
            // Opsional: Jika Anda ingin menghapus data input lainnya (seperti WA)
            $this->participantsData[$id]['wa'] = null;
            $this->participantsData[$id]['dept_head_id'] = null;
        }
    }
    public function render()
    {
        $employees = User::search(trim($this->search))->paginate(10);

        // Pencarian untuk Supervisor
        $currentSearchSpv = '';
        if ($this->activeSpvRowId && isset($this->searchSupervisor[$this->activeSpvRowId])) {
            $currentSearchSpv = $this->searchSupervisor[$this->activeSpvRowId];
        }
        $managers = User::search($currentSearchSpv)->limit(100)->get();

        // Pencarian untuk Dept Head
        $currentSearchDept = '';
        if ($this->activeDeptRowId && isset($this->searchDeptHead[$this->activeDeptRowId])) {
            $currentSearchDept = $this->searchDeptHead[$this->activeDeptRowId];
        }
        $deptHeads = User::search($currentSearchDept)->limit(100)->get();

        return view('livewire.mcu.generate-schedule', [
            'employees' => $employees,
            'managers' => $managers,
            'deptHeads' => $deptHeads
        ]);
    }
}
