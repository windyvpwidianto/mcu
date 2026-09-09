<?php

namespace App\Livewire\Wpi;

use App\Models\User;
use Livewire\Component;
use App\Models\Location;
use App\Models\WpiReport;
use App\Models\Contractor;
use App\Models\Department;
use App\Models\WpiFinding;
use App\Helpers\FileHelper;
use App\Helpers\MailHelper;
use App\Models\WpiWorkflow;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Notifications\WpiSubmittedNotification;

class Index extends Component
{
    use WithFileUploads;
    public $status;
    public string $proceedTo = '';
    public array $availableTransitions = [];
    public string $effectiveRole = '';
    public $asModerator = false;
    public $asErm = false;
    public $assignTo1 = '';
    public $assignTo2 = '';
    public array $ermList = [];
    public $reportId;
    public $report_date, $report_time, $location, $dept_cont, $area;
    public $inspectors = [['name' => '', 'id_number' => '']];
    public $findings = [];
    public $review_date;
    public $review_id;
    public $reviewed_by;

    // Properti Pencarian Umum
    public $locations = [];
    public $show_location = false;
    public $searchLocation = '';
    public $search = '';
    public $departments = [];
    public $departement_id;
    public $showDropdown = false;
    public $searchContractor = '';
    public $contractors = [];
    public $showContractorDropdown = false;
    public $deptCont = 'department';

    // Properti Pencarian Petugas (Independen per Baris)
    public $pelaporsAct = [];
    public $searchPetugas = [];
    public $showDropdownPetugas = [];
    public $manualActPelaporMode = false;
    public $manualActPelaporName = '';
    // Properti Pencarian Petugas (Independen per Baris)
    public $pelapors_pic = [];
    public $search_pic = [];
    public $showDropdown_pic = [];
    public $manualPICPelaporMode = false;
    public $manualPICPelaporName = '';

    public function mount($id = null)
    {
        if ($id) {
            $this->loadData($id);
        } else {

            $this->addFinding();
        }
    }

    public function loadData($id)
    {
        $report = WpiReport::with('findings')->find($id);
        if (!$report) return redirect()->to('/wpi-list');

        // 2. Isi Properti Header
        $this->status = $report->status;
        $this->reportId = $report->id;

        $this->report_date = $report->report_date;

        $this->report_time = date('H:i', strtotime($report->report_time));

        $this->location = $report->location;
        $this->area = $report->area;
        if (Contractor::where('contractor_name', $report->department)->exists()) {
            $this->deptCont = 'company';
            $this->searchContractor = $report->department;
            $this->search = '';
        } elseif (Department::where('department_name', $report->department)->exists()) {
            $this->deptCont = 'department';
            $this->search = $report->department;
            $this->searchContractor = '';
        } else {
            $this->deptCont = 'department';
            $this->search = $report->department;
        }
        // Sinkronisasi data pencarian agar input teks di UI terisi

        $this->searchLocation = $report->location;
        // Jika dept_cont bisa berasal dari Contractor, tambahkan logika pengecekan jika perlu
        $this->dept_cont = $report->department;


        // 3. Isi Properti Inspectors (Array)

        // Asumsi: kolom inspectors di DB disimpan sebagai JSON/Array

        $this->inspectors = $report->inspectors ?? [['name' => '',]];



        // Isi searchPetugas agar input pencarian per baris sinkron

        foreach ($this->inspectors as $index => $inspector) {

            $this->searchPetugas[$index] = $inspector['name'];

            $inspector = User::where('name', $inspector['name'])->first();

            if ($inspector) {

                $this->inspectors[$index]['id_number'] = $inspector->employee_id;

                $this->inspectors[$index]['dept_con'] = $inspector->department_name;
            }
        }

        $this->findings = [];
        foreach ($report->findings as $finding) {
            // Ambil data dari database
            $rawPic = $finding->pic_responsible;
            $existingPics = [];

            if (is_string($rawPic) && !empty($rawPic)) {
                /** * Menggunakan explode dengan '|' karena nama user
                 * mengandung koma (Contoh: "BANEA, Yoman Denis")
                 */
                $existingPics = array_map('trim', explode('|', $rawPic));
            } elseif (is_array($rawPic)) {
                // Jika kolom DB sudah bertipe JSON/Array
                $existingPics = $rawPic;
            }

            $this->findings[] = [
                'id' => $finding->id,
                'ohs_risk' => $finding->ohs_risk,
                'description' => $finding->description,
                'prevention_action' => $finding->prevention_action,

                // Simpan sebagai array agar UI Badge muncul di Blade
                'pic_responsible' => $existingPics,

                'due_date' => $finding->due_date ? date('Y-m-d', strtotime($finding->due_date)) : null,
                'completion_date' => $finding->completion_date ? date('Y-m-d', strtotime($finding->completion_date)) : null,
                'photos' => $finding->photos ?? [],
                'photos_prevention' => $finding->photos_prevention ?? [],
                'new_photos' => [],
                'new_photos_prevention' => [],
            ];
        }
    }

    protected function setEffectiveRole($report)
    {
        $userId = Auth::id();

        // 1. Cek apakah user adalah Submitter (Pembuat laporan)
        $isSubmitter = $report->created_by == $userId;

        // 2. Cek apakah user adalah ERM yang ditugaskan
        // Asumsi: Anda memiliki tabel pivot/relasi wpi_report_erms
        $isErm = DB::table('erm_assignments')
            ->where('user_id', $userId)
            ->where(function ($q) use ($report) {
                // Filter berdasarkan departemen atau kontraktor laporan
                $q->where('department_id', $report->department_id)
                    ->orWhere('contractor_id', $report->contractor_id);
            })->exists();

        // 3. Cek apakah user adalah Moderator
        $isMod = DB::table('moderator_assignments')->where('user_id', $userId)->exists();

        // Tentukan role berdasarkan prioritas untuk workflow
        $currentStatus = $report->status;
        $roles = [];
        if ($isSubmitter) $roles[] = 'Submitter';
        if ($isErm) $roles[] = 'Event Report Manager';
        if ($isMod) $roles[] = 'Moderator';

        // Ambil role yang valid untuk status saat ini dari tabel wpi_workflows
        $allowedRole = WpiWorkflow::whereIn('role', $roles)
            ->where('from_status', $currentStatus)
            ->value('role');

        $this->effectiveRole = $allowedRole ?? '';
        $this->asModerator = $this->effectiveRole === 'Moderator';
        $this->asErm = $this->effectiveRole === 'Event Report Manager';
    }

    protected function loadAvailableTransitions($report)
    {
        $this->availableTransitions = WpiWorkflow::getAvailableTransitions($report->status, $this->effectiveRole);
    }
    protected function loadErmList(): void
    {
        $wpi_report = WpiReport::find($this->reportId);
        $dept = $wpi_report->department_id;
        $cont = $wpi_report->contractor_id;
        $userIds = DB::table('erm_assignments')
            ->select('user_id')
            ->when($dept || $cont, function ($q) use ($dept, $cont) {
                $q->where(function ($q2) use ($dept, $cont) {
                    if ($dept) {
                        $q2->orWhere('department_id', $dept);
                    }
                    if ($cont) {
                        $q2->orWhere('contractor_id', $cont);
                    }
                });
            })
            ->pluck('user_id')
            ->toArray();
        $this->ermList = User::whereIn('id', $userIds)->get()->toArray();
    }

    public function processStatusChange($newStatus)
    {
        // 1. Validasi Input
        if (!$newStatus) {
            $this->dispatch('alert', ['text' => 'Silakan pilih aksi terlebih dahulu.', 'backgroundColor' => 'orange']);
            return;
        }
        if ($newStatus === 'Closed') {
            $reviewed = Auth::user();
            $this->review_date = now()->toDateString();
            $this->review_id = $reviewed->employee_id;
            $this->reviewed_by = $reviewed->name;
        }

        $report = WpiReport::find($this->reportId);

        // 2. Validasi Transisi Workflow (Cek apakah Role user berhak memindahkan status ini)
        if (!WpiWorkflow::isValidTransition($report->status, $newStatus, $this->effectiveRole)) {
            $this->dispatch('alert', ['text' => 'Anda tidak memiliki otoritas untuk aksi ini.', 'backgroundColor' => 'red']);
            return;
        }

        // 3. Logika Khusus Penugasan ERM (Jika Sequence menuju Assigned / InProgress)
        if (in_array($newStatus, ['Assigned', 'InProgress'])) {
            $this->validate([
                'assignTo1' => 'required',
            ], ['assignTo1.required' => 'Pilih ERM Utama untuk menindaklanjuti laporan ini.']);

            // Simpan data penugasan
            $assignIds = array_filter([$this->assignTo1, $this->assignTo2]);
            $report->assignedErms()->sync($assignIds);


            // Kirim Notifikasi ke ERM (Logic yang Anda inginkan)
            $ermUser = User::find($this->assignTo1);
            if ($ermUser) {
                // $ermUser->notify(new WpiAssignedNotification($report));
            }
        }

        // 4. Update Status & Catat di Audit Trail
        // Karena trait LogsActivity aktif, perubahan status ini akan otomatis terekam di modal audit trail
        $report->status = $newStatus;
        $report->review_date = $this->review_date;
        $report->review_id = $this->review_id;
        $report->reviewed_by = $this->reviewed_by;
        $report->save();

        // 5. Reset UI & Beri Feedback
        $this->reset(['proceedTo', 'assignTo1', 'assignTo2']);
        $this->status = $newStatus; // Sync local property

        $this->dispatch('alert', [
            'text' => 'Status Berhasil diperbarui ke ' . $newStatus,
            'backgroundColor' => "linear-gradient(to right, #00c853, #00bfa5)"
        ]);

        // Refresh data untuk mengupdate tombol aksi yang tersedia selanjutnya
        $this->loadData($this->reportId);
    }

    /**
     * Logika Pencarian Petugas Inspeksi (Multi-row)
     */
    public function updatedSearchPic($value, $key)
    {
        // Ambil index dari key, misal "searchPetugas.0" -> index = 0
        $index = explode('.', $key)[0];

        if (strlen($value) > 1) {
            $this->pelapors_pic = User::where('name', 'like', '%' . $value . '%')
                ->orderBy('name')
                ->limit(20)
                ->get();
            $this->showDropdown_pic[$index] = true;
        } else {
            $this->showDropdown_pic[$index] = false;
        }
    }

    public function selectPicPelapor($id, $name)
    {
        // Cari index baris mana yang dropdown-nya aktif
        $index = collect($this->showDropdown_pic)->search(true);

        if ($index !== false) {
            // 1. Pastikan pic_responsible adalah array
            if (!is_array($this->findings[$index]['pic_responsible'])) {
                $this->findings[$index]['pic_responsible'] = [];
            }

            // 2. Tambahkan nama jika belum ada di list (mencegah duplikat)
            if (!in_array($name, $this->findings[$index]['pic_responsible'])) {
                $this->findings[$index]['pic_responsible'][] = $name;
            }

            // 3. Reset input pencarian agar user bisa cari nama lain
            $this->search_pic[$index] = '';
            $this->showDropdown_pic[$index] = false;
        }
    }

    // Method untuk menghapus salah satu PIC yang sudah dipilih
    public function removePic($findingIndex, $picIndex)
    {
        unset($this->findings[$findingIndex]['pic_responsible'][$picIndex]);
        $this->findings[$findingIndex]['pic_responsible'] = array_values($this->findings[$findingIndex]['pic_responsible']);
    }

    /**
     * Logika Pencarian Petugas Inspeksi (Multi-row)
     */
    public function updatedSearchPetugas($value, $key)
    {
        // Ambil index dari key, misal "searchPetugas.0" -> index = 0
        $index = explode('.', $key)[0];

        if (strlen($value) > 1) {
            $this->pelaporsAct = User::where('name', 'like', '%' . $value . '%')
                ->orderBy('name')
                ->limit(20)
                ->get();
            $this->showDropdownPetugas[$index] = true;
        } else {
            $this->showDropdownPetugas[$index] = false;
        }
    }

    public function selectActPelapor($id, $name)
    {
        // Cari index mana yang dropdown-nya sedang terbuka
        $index = collect($this->showDropdownPetugas)->search(true);
        if ($index !== false) {
            // 1. Simpan data ke array findings sesuai barisnya
            $this->inspectors[$index]['name'] = $name;
            $inspector = User::where('name', $name)->first();
            if ($inspector) {
                $this->inspectors[$index]['id_number'] = $inspector->employee_id;
                $this->inspectors[$index]['dept_con'] = $inspector->department_name;
            }

            // 2. Update search input agar sinkron di UI
            $this->searchPetugas[$index] = $name;

            // 3. Tutup dropdown untuk baris tersebut
            $this->showDropdownPetugas[$index] = false;
        }
    }

    public function addInspector()
    {
        if (count($this->inspectors) < 6) {
            $this->inspectors[] = [
                'name' => '',
                'id_number' => '',
                'dept_con' => ''
            ];
            $this->searchPetugas[] = '';
            $this->showDropdownPetugas[] = false;
        } else {
            $this->dispatch('alert', [
                'text' => 'Maksimal 6 petugas inspeksi.',
                'backgroundColor' => "linear-gradient(to right, #ef4444, #991b1b)",
            ]);
        }
    }

    public function removeInspector($index)
    {
        unset($this->inspectors[$index]);
        unset($this->searchPetugas[$index]);
        unset($this->showDropdownPetugas[$index]);

        $this->inspectors = array_values($this->inspectors);
        $this->searchPetugas = array_values($this->searchPetugas);
        $this->showDropdownPetugas = array_values($this->showDropdownPetugas);
    }

    /**
     * Logika Lokasi, Department, dan Contractor
     */
    public function updatedSearchLocation()
    {
        if (strlen($this->searchLocation) > 2) {
            $this->locations = Location::where('name', 'like', '%' . $this->searchLocation . '%')
                ->orderBy('name')->limit(100)->get();
            $this->show_location = true;
        } else {
            $this->show_location = false;
        }
        $this->reset('location');
    }

    public function selectLocation($id, $name)
    {
        $this->location = $name;
        $this->searchLocation = $name;
        $this->show_location = false;
    }

    public function updatedSearch()
    {
        if (strlen($this->search) > 1) {
            $this->departments = Department::where('department_name', 'like', '%' . $this->search . '%')
                ->orderBy('department_name')->limit(80)->get();
            $this->showDropdown = true;
        } else {
            $this->showDropdown = false;
        }
    }

    public function selectDepartment($id, $name)
    {
        $this->reset('searchContractor');
        $this->search = $name;
        $this->dept_cont = $name;
        $this->showDropdown = false;
        $this->validateOnly('dept_cont', [
            'dept_cont' => 'required',
        ]);
    }
    public function updatedSearchContractor()
    {
        if (strlen($this->searchContractor) > 1) {
            $this->contractors = Contractor::query()
                ->where('contractor_name', 'like', '%' . $this->searchContractor . '%')
                ->orderBy('contractor_name')
                ->limit(80)
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
        $this->dept_cont = $name;
        $this->searchContractor = $name;
        $this->showContractorDropdown = false;
        $this->validateOnly('dept_cont', [
            'dept_cont' => 'required',
        ]);
    }

    /**
     * Logika Findings dan File Upload
     */
    public function addFinding()
    {
        $this->findings[] = [
            'ohs_risk' => 'L',
            'description' => '',
            'prevention_action' => '',
            'pic_responsible' => [], // Ubah dari '' menjadi []
            'due_date' => '',
            'completion_date' => '',
            'photos' => [],
            'photos_prevention' => [],
            'new_photos' => [],
            'new_photos_prevention' => [],
        ];
    }

    public function updatedFindings($value, $key)
    {
        if (str_ends_with($key, '.new_photos')) {
            $this->validateOnly($key, [
                'findings.*.new_photos.*' => 'image|max:2048',
            ]);
        }
        if (str_ends_with($key, '.new_photos_prevention')) {
            $this->validateOnly($key, [
                'findings.*.new_photos_prevention.*' => 'image|max:2048',
            ]);
        }
    }
    public function removeFinding($index)
    {
        // 1. Cek apakah baris ini sudah ada di database (untuk mode Edit)
        // Jika ada, kita mungkin perlu menghapus file fisiknya dari storage
        if (isset($this->findings[$index]['id'])) {
            $finding = WpiFinding::find($this->findings[$index]['id']);
            if ($finding && $finding->photos) {
                foreach ($finding->photos as $path) {
                    // Gunakan helper untuk hapus file agar storage tidak penuh
                    FileHelper::deleteFile($path);
                }
            }
            if ($finding && $finding->photos_prevention) {
                foreach ($finding->photos_prevention as $path) {
                    // Gunakan helper untuk hapus file agar storage tidak penuh
                    FileHelper::deleteFile($path);
                }
            }
        }

        // 2. Hapus baris dari array findings
        unset($this->findings[$index]);

        // 3. Reset index array agar tetap berurutan (0, 1, 2...)
        // Penting agar wire:key tidak error
        $this->findings = array_values($this->findings);

        // 4. Opsional: Pastikan minimal selalu ada 1 baris
        if (empty($this->findings)) {
            $this->addFinding();
        }
    }
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'report_date' => 'required|date',
            'report_time' => 'required',
            'location'    => 'required',
            'dept_cont'   => 'required',
            'area'        => 'required',

            // Validation untuk Array Inspectors
            'inspectors'             => 'required|array|min:1',
            'inspectors.*.name'      => 'required|string|min:3',
            'inspectors.*.id_number' => 'required',

            // Validation untuk Array Findings
            'findings.*.description'       => 'required',
            'findings.*.prevention_action' => 'required|string',
            'findings.*.due_date'            => 'nullable|date',
        ], [
            // Custom Messages
            'report_date.required' => 'Tanggal laporan wajib diisi',
            'report_time.required' => 'Waktu laporan wajib diisi',
            'report_date.date'     => 'Format tanggal tidak valid',
            'location.required'    => 'Lokasi wajib dipilih',
            'area.required'        => 'Area wajib diisi',
            'dept_cont.required'    => 'Departemen atau Kontraktor wajib diisi',

            'inspectors.required'          => 'Minimal harus ada 1 petugas inspeksi',
            'inspectors.*.name.required'   => 'Nama petugas inspeksi wajib diisi',
            'inspectors.*.name.min'        => 'Nama petugas minimal 3 karakter',
            'findings.*.due_date.date'        => 'Format tanggal tidak valid',
            'findings.*.due_date.required'        => 'Tanggal jatuh tempo wajib diisi',

            'findings.*.description.required'       => 'Deskripsi temuan wajib diisi',
            'findings.*.prevention_action.required' => 'Tindakan pencegahan wajib diisi',

        ]);
    }
    // Menghapus foto yang baru diunggah (masih di memori/temporary)
    public function removeTempPhoto($findingIndex, $fileKey)
    {
        if (isset($this->findings[$findingIndex]['new_photos'][$fileKey])) {
            unset($this->findings[$findingIndex]['new_photos'][$fileKey]);
            // Re-index array agar tidak ada key yang melompat
            $this->findings[$findingIndex]['new_photos'] = array_values($this->findings[$findingIndex]['new_photos']);
        }
    }

    // Menghapus foto yang sudah tersimpan di database (permanent)
    public function removeSavedPhoto($findingIndex, $photoKey)
    {
        if (isset($this->findings[$findingIndex]['photos'][$photoKey])) {
            $pathToDelete = $this->findings[$findingIndex]['photos'][$photoKey];

            // 1. Hapus file fisik via Helper
            FileHelper::deleteFile($pathToDelete);

            // 2. Update array state
            unset($this->findings[$findingIndex]['photos'][$photoKey]);
            $this->findings[$findingIndex]['photos'] = array_values($this->findings[$findingIndex]['photos']);

            // 3. Update database jika record sudah ada
            if (isset($this->findings[$findingIndex]['id'])) {
                WpiFinding::where('id', $this->findings[$findingIndex]['id'])
                    ->update(['photos' => $this->findings[$findingIndex]['photos']]);
            }
        }
    }
    public function downloadFile($path)
    {
        // Validasi keberadaan file di disk public
        if (Storage::disk('public')->exists($path)) {
            // Mengembalikan response download langsung
            return Storage::disk('public')->download($path);
        }

        // Berikan alert jika file tidak ditemukan
        $this->dispatch('alert', [
            'text' => 'File tidak ditemukan di server.',
            'backgroundColor' => "linear-gradient(to right, #ef4444, #991b1b)",
        ]);
    }

    // Menghapus foto pencegahan sementara
    public function removeTempPhotoPrevention($findingIndex, $fileKey)
    {
        if (isset($this->findings[$findingIndex]['new_photos_prevention'][$fileKey])) {
            unset($this->findings[$findingIndex]['new_photos_prevention'][$fileKey]);
            $this->findings[$findingIndex]['new_photos_prevention'] = array_values($this->findings[$findingIndex]['new_photos_prevention']);
        }
    }

    // Menghapus foto pencegahan permanen dari DB & Storage
    public function removeSavedPhotoPrevention($findingIndex, $photoKey)
    {
        if (isset($this->findings[$findingIndex]['photos_prevention'][$photoKey])) {
            $pathToDelete = $this->findings[$findingIndex]['photos_prevention'][$photoKey];

            // Hapus file fisik
            FileHelper::deleteFile($pathToDelete);

            // Update array state
            unset($this->findings[$findingIndex]['photos_prevention'][$photoKey]);
            $this->findings[$findingIndex]['photos_prevention'] = array_values($this->findings[$findingIndex]['photos_prevention']);

            // Update database jika sudah ada ID
            if (isset($this->findings[$findingIndex]['id'])) {
                WpiFinding::where('id', $this->findings[$findingIndex]['id'])
                    ->update(['photos_prevention' => $this->findings[$findingIndex]['photos_prevention']]);
            }
        }
    }
    public function save()
    {
        $this->validate([
            'report_date' => 'required|date',
            'report_time' => 'required',
            'location' => 'required',
            'area' => 'required',
            'findings.*.description' => 'required',
            'findings.*.prevention_action' => 'required|string',
            'findings.*.due_date' => 'required|date',
            'findings.*.pic_responsible' => 'required|array|min:1',
            'inspectors' => 'required|array|min:1',
            'inspectors.*.name' => 'required|string|min:3',
            'dept_cont' => 'required',
        ]);

        // 1. Tentukan data tambahan untuk workflow
        $workflowData = [
            'report_date' => $this->report_date,
            'report_time' => $this->report_time,
            'location'    => $this->location,
            'area'        => $this->area,
            'department'  => $this->dept_cont,
            'inspectors'  => $this->inspectors,
            'department_id' => $this->deptCont === 'department' ? Department::where('department_name', $this->dept_cont)->first()?->id : null,
            'contractor_id' => $this->deptCont === 'company' ? Contractor::where('contractor_name', $this->dept_cont)->first()?->id : null,
            'review_date' => $this->review_date,
            'review_id' => $this->review_id,
            'reviewed_by' => $this->reviewed_by,
        ];

        // 2. Set Status dan Pembuat jika ini adalah laporan baru
        if (!$this->reportId) {
            $workflowData['status'] = 'Submitted';
            $workflowData['created_by'] = auth()->id();
        }

        $lastReport = WpiReport::latest('id')->first();
        $nextId = $lastReport ? $lastReport->id + 1 : 1;
        $referenceNumber = 'WPI-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        if (!$this->reportId) {
            $workflowData['no_referensi'] = $referenceNumber;
        }

        $report = WpiReport::updateOrCreate(
            ['id' => $this->reportId],
            $workflowData
        );

        // 3. Kelola Findings & Notifikasi PIC (Anti-Spam)
        $existingFindingIds = $report->findings()->pluck('id')->toArray();
        $currentFindingIds = [];
        $sentEmailsTo = []; // Array pelacak agar tidak double email ke orang yang sama

        foreach ($this->findings as $finding) {
            $photoPaths = $finding['photos'] ?? [];
            $photoPrevention = $finding['photos_prevention'] ?? [];

            // Upload Photos Baru
            if (!empty($finding['new_photos'])) {
                foreach ($finding['new_photos'] as $photo) {
                    $photoPaths[] = FileHelper::compressAndStore($photo, 'wpi-photos', 800, 75);
                }
            }
            if (!empty($finding['new_photos_prevention'])) {
                foreach ($finding['new_photos_prevention'] as $photo) {
                    $photoPrevention[] = FileHelper::compressAndStore($photo, 'wpi-photos-prevention', 800, 75);
                }
            }

            $picValue = is_array($finding['pic_responsible'])
                ? implode('|', $finding['pic_responsible'])
                : $finding['pic_responsible'];

            $findingModel = $report->findings()->updateOrCreate(
                ['id' => $finding['id'] ?? null],
                [
                    'ohs_risk' => $finding['ohs_risk'],
                    'description' => $finding['description'],
                    'prevention_action' => $finding['prevention_action'],
                    'pic_responsible' => $picValue,
                    'due_date' => $finding['due_date'] ? date('Y-m-d', strtotime($finding['due_date'])) : null,
                    'completion_date' => $finding['completion_date'] ? date('Y-m-d', strtotime($finding['completion_date'])) : null,
                    'photos' => $photoPaths,
                    'photos_prevention' => $photoPrevention,
                ]
            );

            $currentFindingIds[] = $findingModel->id;

            // --- Logika Notifikasi PIC per Temuan ---
            $currentPicNames = is_array($finding['pic_responsible'])
                ? $finding['pic_responsible']
                : explode('|', $finding['pic_responsible']);

            $currentPicNames = array_filter(array_unique($currentPicNames));

            if (!empty($currentPicNames)) {
                $picUsers = User::whereIn('name', $currentPicNames)->get();
                foreach ($picUsers as $user) {
                    // Cek apakah user ini sudah dikirimi email untuk laporan ini
                    if (!in_array($user->id, $sentEmailsTo)) {
                        $areaName = $report->area ?? 'General Area';

                        MailHelper::sendToUserId(
                            $user->id,
                            $this->reportId ? "Update Penugasan PIC: $report->no_referensi" : "Penugasan Temuan WPI: $report->no_referensi",
                            'emails.notification',
                            [
                                'subject'        => "Tindakan Perbaikan WPI: $report->no_referensi",
                                'title'          => "Halo {$user->name},",
                                'messageText'    => $this->reportId
                                    ? "Terdapat pembaruan pada temuan WPI di mana Anda ditugaskan sebagai PIC."
                                    : "Anda telah ditunjuk sebagai PIC untuk menindaklanjuti temuan pada laporan WPI baru.",
                                'additionalInfo' => "Nomor Laporan: $report->no_referensi\nLokasi: $areaName\nStatus: $report->status",
                                'actionUrl'      => route('wpi.edit', $report->id)
                            ]
                        );
                        $sentEmailsTo[] = $user->id; // Tandai agar tidak dikirim lagi
                    }
                }
            }
        }

        // PROSES DELETE
        $findingsToDelete = array_diff($existingFindingIds, $currentFindingIds);
        if (!empty($findingsToDelete)) {
            WpiFinding::whereIn('id', $findingsToDelete)->get()->each->delete();
        }

        // 4. Notifikasi ke Moderator & Inspector (Logika Existing)
        $area = $report->area ?? 'General Area';
        $reporterName = $report->creator->name ?? 'System';

        if (!$this->reportId) {
            // Moderator Baru
            $moderatorIds = WpiWorkflow::getModeratorsForStatus('Submitted', $report);
            foreach ($moderatorIds as $userId) {
                MailHelper::sendToUserId($userId, 'Laporan WPI Baru', 'emails.notification', [
                    'subject' => 'Laporan WPI Baru',
                    'title' => 'Notifikasi Laporan Baru',
                    'messageText' => "Telah dibuat laporan WPI baru oleh $reporterName.",
                    'additionalInfo' => "No: $report->no_referensi\nArea: $area",
                    'actionUrl' => route('wpi.edit', $report->id)
                ]);
            }
        }

        $this->dispatch('alert', [
            'text' => $this->reportId ? 'Data berhasil diperbarui' : 'Data berhasil disimpan',
            'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
        ]);

        if ($this->reportId) {
            $this->loadData($this->reportId);
            if (in_array($this->proceedTo, ['Assigned', 'Review Event'])) {
                $this->processStatusChange($this->proceedTo);
                $report->refresh();
            }

            // Notifikasi Update Status ke Moderator
            $currentStatus = $report->status;
            $moderatorIds = WpiWorkflow::getModeratorsForStatus($currentStatus, $report);
            foreach ($moderatorIds as $userId) {
                MailHelper::sendToUserId($userId, "Update WPI: $currentStatus", 'emails.notification', [
                    'subject' => "Status WPI $report->no_referensi Updated",
                    'title' => 'Update Status',
                    'messageText' => "Status saat ini: **$currentStatus**.",
                    'additionalInfo' => "No: $report->no_referensi\nPelapor: $reporterName",
                    'actionUrl' => route('wpi.edit', $report->id)
                ]);
            }
        } else {
            return $this->redirect(route('wpi.edit', $report->id), navigate: true);
        }
    }
    #[On('trigger-export-pdf')]
    public function exportPDF($id)
    {
        $report = WpiReport::with(['findings'])->findOrFail($id);
        $no_referensi = $report->no_referensi;
        $isContractor = Contractor::where('contractor_name', $report->department)->exists();
        $deptLabel = $isContractor ? 'Contractor' : 'Department';
        $pdf = Pdf::loadView('pdf.wpi-report', compact('report', 'deptLabel', 'no_referensi'))
            ->setOption([
                'isPhpEnabled' => true,
                'isRemoteEnabled' => true
            ])
            ->setPaper('a4', 'portrait');
        // 2. Render PDF terlebih dahulu agar bisa mengakses Canvas
        $pdf->render();
        $canvas = $pdf->getCanvas();
        $font = null; // Ini akan otomatis menggunakan font default PDF (Helvetica/Times-Roman)
        $size = 9;
        /**
         * Parameter page_text:
         * (X, Y, Text, Font, Size, Color)
         * Untuk Landscape A4: X = 730 (Kanan), Y = 560 (Bawah)
         */
        $canvas->page_text(455, 788, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, $size, [0, 0, 0]);

        return response()->streamDownload(function () use ($pdf) {
            // Menggunakan output() memastikan seluruh script penomoran diproses
            echo $pdf->output();
        }, "Laporan_WPI.pdf");
    }

    public function render()
    {
        if ($this->reportId) {
            $report = WpiReport::find($this->reportId);
            $this->setEffectiveRole($report);
            $this->loadAvailableTransitions($report);
            $this->loadErmList();
        }
        return view('livewire.wpi.index');
    }
    public function getRandomBadgeColor($status)
    {
        return match ($status) {
            'Submitted' => 'badge-info',
            'Final Review'   => 'badge-warning',
            'Closed'    => 'badge-success',
            'Cancelled' => 'badge-error',
            'Assigned' => 'badge-primary',
            'Review Event' => 'badge-primary',
            default     => 'badge-ghost',
        };
    }
}
