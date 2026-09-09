<?php

namespace App\Livewire\Incident;

use \App\Traits\WithDeptContSelection;
use App\Helpers\DateBeforeOrEqualToday;
use App\Helpers\FileHelper;
use App\Helpers\MailHelper;
use App\Models\BodyPart;
use App\Models\Contractor;
use App\Models\Department;
use App\Models\EventSubType;
use App\Models\EventType;
use App\Models\IncidentReport;
use App\Models\Likelihood;
use App\Models\ModeratorAssignment;
use App\Models\RiskAssessment;
use App\Models\RiskAssessmentMatrix;
use App\Models\RiskConsequence;
use App\Models\RiskMatrixCell;
use App\Models\UnsafeAct;
use App\Models\UnsafeCondition;
use App\Models\User;
use App\Traits\WithSearchLocation;
use App\Traits\WithSearchPelapor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Create extends Component
{
    use WithFileUploads, WithPagination, WithDeptContSelection, WithSearchLocation, WithSearchPelapor;

    public $event_type_id, $event_sub_type_id, $description, $location_id, $location_specific, $title;
    public $date_time, $pelapor_id, $manualPelaporName;
    public $department_id, $contractor_id, $penanggungJawab;
    public $deptCont = 'dept';
    public $keyWord = 'kta';
    public $likelihood_id, $consequence_id, $emergency_action;
    public $damage_detail;

    public $likelihoods = [], $consequences = [],
        $location_spesific,
        $documentation,
        $incident_photo_path,
        $supporting_documents_path;
    #[Url(as: 'step')]


    public $selectedLikelihoodId, $selectedConsequenceId;
    public $RiskAssessment;
    public $risk_consequence;
    public $env_classification, $contract_area_name;
    public $penanggungJawabOptions = [];
    // Involved Personnel
    public $involved_personnel_id, $searchName, $involved_personnel_name;
    public $showinvolvedPersonnelDropdown = false;
    public $involvedPersonnelManualMode = false;
    public $manualMode = []; // Array untuk menyimpan status manual per index
    public $manualEmployeeName = [];
    public $involved_personnel = []; // Array utama untuk menampung banyak korban
    public $selected_personnel = [];
    public $showBodyPart = false;
    public $body_part_id;
    public $body_part_name;
    public $corrective_actions = [];
    public array $directly_involved = [];
    public array $searchKorban = []; // Menyimpan text pencarian per baris
    public array $show_employee_dropdown = []; // Menyimpan state open/close dropdown per baris
    public $involved_personnel_options = [];

    // State untuk menyimpan baris data
    public $incident_photo_paths = []; // Ubah dari string ke array
    public $supporting_documents_paths = []; // Ubah dari string ke array
    public $options = [];

    // Data Utama
    public $incident_photo = [];
    public $supporting_documents = [];      // Hasil query pencarian (biasanya global atau di-filter)

    public $selectedBodyParts = []; // Sekarang menjadi array
    public $selectedBodyPartCategory = null;
    public $tasks, $potential_lti;


    public function rules()
    {
        // PART 1 & 2: Rules Utama yang WAJIB ada saat Create
        $rules = [
            // PART 1
            'title' => 'required|string|max:255',
            'event_type_id' => 'required|exists:event_types,id',
            'event_sub_type_id' => 'required|exists:event_sub_types,id',
            'potential_lti' => 'required|in:Yes,No',
            'description' => 'required|string',
            'tasks' => 'required|string|min:1',
            'location_id' => 'required|exists:locations,id',
            'location_specific' => 'required|string',
            'contract_area_name' => 'required|string',
            'env_classification' => $this->isEnvironmentType ? 'required|string' : 'nullable',
            'date_time' => 'required|date|before_or_equal:now',
            'pelapor_id' => 'required_without:manualPelaporName',
            'department_id' => 'nullable|required_without:contractor_id|exists:departments,id',
            'contractor_id' => 'nullable|required_without:department_id|exists:contractors,id',
            'deptCont' => 'required',
            'keyWord' => 'required',
            'likelihood_id' => 'required',
            'consequence_id' => 'required',
            'emergency_action' => 'required',
            'penanggungJawab' => 'required',

            // PART 2: Pihak Terlibat Langsung
            'directly_involved' => 'required|array|min:1',
            'directly_involved.*.employee_name' => 'required|string',
            'directly_involved.*.employee_nik'  => 'required',
            'directly_involved.*.dept_cont'     => 'required',
            'directly_involved.*.jabatan'       => 'required',
            'directly_involved.*.roster'        => 'required',
            'directly_involved.*.sift'          => 'required',
            'directly_involved.*.keterlibatan'  => 'required',
            'directly_involved.*.pengalaman_kerja' => 'required|numeric',

            // Logika Kondisional Injury / Damage
            'selectedBodyPartCategory' => $this->isInjury ? 'required' : 'nullable',
            'selectedBodyParts' => $this->isInjury ? 'required' : 'nullable',
            'selectedBodyParts.*' => 'exists:body_parts,id',
            'damage_detail' => !$this->isInjury ? 'required|string' : 'nullable',
        ];
        return $rules;
    }
    /**
     * Mengembalikan atribut validasi yang sudah diterjemahkan.
     */
    protected function validationAttributes()
    {
        $attributes = [
            'title' => __('Judul Laporan'),
            'pelapor_id'        => __('Nama Pelapor'),
            'manualPelaporName' => __('Nama Pelapor Manual'),
            'event_type_id'     => __('Tipe Kejadian'),
            'event_sub_type_id' => __('Sub Tipe Kejadian'),
            'tasks'             => __('Tugas/Tindakan Cepat'),
            'potential_lti'     => __('Potensi LTI/Fatality'),
            'description'       => __('Deskripsi Kejadian'),
            'location_id'       => __('Lokasi Utama'),
            'contract_area_name' => __('Area Kontrak Karya'),
            'env_classification' => __('Klasifikasi Lingkungan'),
            'location_specific' => __('Detail Lokasi Spesifik'),
            'date_time'         => __('Tanggal dan Waktu'),
            'keyWord'           => __('Jenis Bahaya'),
            'department_id'     => __('Departemen'),
            'contractor_id'     => __('Perusahaan Kontraktor'),
            'deptCont'          => __('Pihak Terlibat'),
            'penanggungJawab'   => __('PIC / Penanggung Jawab'),
            'likelihood_id'     => __('Kemungkinan (Likelihood)'),
            'consequence_id'    => __('Konsekuensi (Consequence)'),
            'emergency_action'  => __('Tindakan Darurat'),
            'selectedBodyPartCategory' => __('Kategori Bagian Tubuh'),
            'selectedBodyParts'         => __('Detail Bagian Tubuh'),
            'damage_detail'            => __('Detail Kerusakan'),

            // Part 2
            'directly_involved.*.employee_name' => __('Nama Personel'),
            'directly_involved.*.employee_nik'  => __('NIK/ID'),
            'directly_involved.*.dept_cont'     => __('Departemen/Perusahaan'),
            'directly_involved.*.jabatan'       => __('Jabatan'),
            'directly_involved.*.roster'        => __('Roster'),
            'directly_involved.*.sift'          => __('Shift'),
            'directly_involved.*.keterlibatan'  => __('Jenis Keterlibatan'),
            'directly_involved.*.pengalaman_kerja' => __('Pengalaman Kerja'),
        ];


        return $attributes;
    }

    public function updated($propertyName)
    {
        // 1. Simpan ke Session setiap ada perubahan
        $this->saveToSession();

        // 2. Validasi Real-time
        // Khusus untuk event_type_id, validasi juga field dampak karena saling bergantung
        if ($propertyName === 'event_type_id') {
            $this->validateOnly('selectedBodyPartCategory');
            $this->validateOnly('selectedBodyParts');
            $this->validateOnly('damage_detail');
        }

        // Validasi field yang sedang diubah
        try {
            $this->validateOnly($propertyName);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Biarkan Livewire menangani error display
        }
    }

    protected function saveToSession()
    {
        // Mengambil data property penting saja untuk session
        $data = $this->all();

        // Hapus objek yang tidak bisa diserialisasi (File Uploads)
        unset(
            $data['incident_photo'],
            $data['supporting_documents'],
            $data['incident_photo_paths'],
            $data['supporting_documents_paths']
        );

        session()->put('incident_create_data', $data);
    }

    // Komentar Standard
    protected function messages()
    {
        return [
            // Pesan Standar
            'required' => __(':attribute wajib diisi.'),
            'exists'   => __('Pilihan :attribute tidak valid.'),
            'min'      => __(':attribute minimal harus :min karakter.'),
            'date'     => __('Format tanggal :attribute tidak sesuai.'),
            'after_or_equal' => __(':attribute tidak boleh tanggal lampau.'),
            'date_time.before_or_equal' => __('Waktu kejadian tidak boleh melebihi waktu saat ini.'),
            // --- PART 7: DOKUMENTASI ---
            'supporting_documents.*.mimes' => __('Hanya file PDF dan Word yang diperbolehkan.'),
            'supporting_documents.*.max'   => __('Ukuran file dokumen tidak boleh lebih dari 5MB.'),

            'incident_photo.required' => __('Bukti visual wajib dilampirkan.'),
            'incident_photo.*.image'  => __('File harus berupa gambar (JPG, PNG, WebP).'),
            'incident_photo.*.mimes'  => __('Format file tidak didukung. Gunakan JPG atau PNG.'),
            'incident_photo.*.max'    => __('Ukuran foto maksimal 2MB.'),

            // --- PART 7: TINDAKAN PERBAIKAN ---
            'corrective_actions.*.action_description.required' => __('Rencana perbaikan wajib diisi.'),
            'corrective_actions.*.action_description.min'      => __('Deskripsi rencana perbaikan terlalu singkat.'),
            'corrective_actions.*.control_hierarchy.required'  => __('Pilih salah satu hirarki kontrol.'),
            'corrective_actions.*.pic_user_id.required'               => __('PIC (Penanggung Jawab) wajib dipilih.'),
            'corrective_actions.*.due_date.required'           => __('Batas waktu (Due Date) wajib diisi.'),
            'corrective_actions.*.actual_completion_date.required'           => __('Batas waktu (Due Date) wajib diisi.'),
            // Part 8
            'key_learning.required' => __('Kunci pembelajaran wajib diisi sebagai bahan evaluasi.'),
            'key_learning.min' => __('Mohon berikan penjelasan kunci pembelajaran yang lebih detail (min. 10 karakter).'),

            // --- PESAN KHUSUS LOGIKA SENTRY ---

            'department_id.required_without'       => __('Silakan pilih Departemen atau Kontraktor.'),
            'contractor_id.required_without'       => __('Pilih Kontraktor atau Department terkait.'),
        ];
    }

    public $currentStep = 1; // Pastikan dimulai dari 1
    public $totalSteps = 2;

    public function validateCurrentStep()
    {


        $fields = [];
        switch ($this->currentStep) {
            case 1:
                $fields = [
                    'title',
                    'event_type_id',
                    'event_sub_type_id',
                    'incident_photo',
                    'description',
                    'tasks',
                    'potential_lti',
                    'location_id',
                    'contract_area_name',
                    'env_classification',
                    'location_specific',
                    'date_time',
                    'pelapor_id',
                    'department_id',
                    'contractor_id',
                    'deptCont',
                    'keyWord',
                    'likelihood_id',
                    'consequence_id',
                    'emergency_action',
                    'penanggungJawab',
                    'selectedBodyPartCategory',
                    'selectedBodyParts',
                    'damage_detail'
                ];
                break;

            case 2:
                $fields = [
                    'directly_involved',
                    'directly_involved.*.employee_name',
                    'directly_involved.*.employee_nik',
                    'directly_involved.*.dept_cont',
                    'directly_involved.*.jabatan',
                    'directly_involved.*.roster',
                    'directly_involved.*.sift',
                    'directly_involved.*.keterlibatan',
                    'directly_involved.*.pengalaman_kerja',
                ];
                break;
        }

        if (!empty($fields)) {
            $allRules = $this->rules();
            $stepRules = [];
            foreach ($fields as $field) {
                if (isset($allRules[$field])) {
                    $stepRules[$field] = $allRules[$field];
                }
            }
            $this->validate($stepRules, $this->messages(), $this->validationAttributes());
        }
    }
    public function goToStep($step)
    {
        // Jika user mencoba lompat ke step di depannya, validasi dulu step sekarang
        if ($step > $this->currentStep) {
            $this->validateCurrentStep();
        }

        $this->currentStep = $step;
    }
    // Fungsi untuk pindah step
    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
            $this->dispatch('scroll-to-top');
        }
    }


    public function removeFile($index)
    {
        // Hapus file fisik jika perlu
        if (isset($this->saved_photos[$index])) {
            FileHelper::deleteFile($this->saved_photos[$index]);
            unset($this->saved_photos[$index]);

            // Re-index array agar tidak berantakan
            $this->saved_photos = array_values($this->saved_photos);
        }
    }
    public function selectInvolvedPersonnel($id, $name, $index)
    {
        // 1. Cari data karyawan di database
        // Sesuaikan model 'Employee' dengan model yang Anda gunakan
        $employee = User::find($id);

        if ($employee) {
            // 2. Isi data ke array directly_involved berdasarkan index-nya
            $this->directly_involved[$index]['employee_name'] = $employee->name;
            $this->directly_involved[$index]['employee_id']   = $employee->id; // Untuk tracking ID
            $this->directly_involved[$index]['employee_nik']  = $employee->employee_id;

            // Asumsi relasi department atau kolom string dept
            $this->directly_involved[$index]['dept_cont']     = $employee->department_name ?? '';
            $this->directly_involved[$index]['jabatan']       = $employee->position;

            // 3. Reset dropdown search untuk baris ini
            $this->show_employee_dropdown[$index] = false;
            $this->searchKorban[$index] = $employee->name;

            // 4. PENTING: Simpan perubahan ke session agar tidak hilang saat refresh
            session(['incident_create_data' => $this->all()]);

            // 5. Opsional: Trigger validasi untuk baris tersebut
            $this->validateOnly("directly_involved.$index.*");
        }
    }

    // Tambahkan "= null" pada parameter $index
    public function enableManualMode($index)
    {

        if ($index === null) return; // Proteksi tambahan

        $this->manualMode[$index] = true;
        $this->manualEmployeeName[$index] = $this->searchKorban[$index] ?? '';
    }

    public function addManualData($index = null)
    {

        if ($index === null) return;

        if (empty($this->manualEmployeeName[$index])) {
            return;
        }

        $this->directly_involved[$index]['employee_name'] = $this->manualEmployeeName[$index];
        $this->directly_involved[$index]['employee_id']   = null;
        $this->directly_involved[$index]['employee_nik']  = 'isi manual';

        $this->searchKorban[$index] = $this->manualEmployeeName[$index];
        $this->show_employee_dropdown[$index] = false;
        $this->manualMode[$index] = false;

        $this->saveToSession();
    }

    // State untuk menampilkan dropdown

    public function mount()
    {
        // 1. Load data referensi statis (Paling aman ditaruh di atas)
        $this->likelihoods = Likelihood::orderByDesc('level')->get();
        $this->consequences = RiskConsequence::orderBy('level')->get();

        // 2. PRIORITAS UTAMA: Ambil data dari Session jika ada
        if (session()->has('incident_create_data')) {
            $data = session('incident_create_data');
            $this->fill($data);


        }

        // 3. INISIALISASI DEFAULT (Hanya jika data masih kosong / belum ada di session)

        // Inisialisasi Pihak Terlibat
        if (empty($this->directly_involved)) {
            $this->addDirectlyInvolvedRow();
        }



    }


    public function updatedDeptCont($value)
    {
        if ($value === 'department') {
            $this->contractor_id = null;
            $this->searchContractor = '';
        } else {
            $this->department_id = null;
            $this->search = '';
        }
    }
    public $saved_photos = [];
    public function updatedIncidentPhoto()
    {
        try {
            // 1. Validasi file yang baru masuk
            $this->validateOnly('incident_photo.*', [
                'incident_photo.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            ], [
                'incident_photo.*.image' => 'File harus berupa gambar.',
                'incident_photo.*.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
                'incident_photo.*.max'   => 'Ukuran foto maksimal 2MB.',
            ]);

            // 2. Jika valid, langsung proses & pindahkan ke storage permanen/semi-permanen
            foreach ($this->incident_photo as $file) {
                $path = FileHelper::compressAndStore(
                    $file,
                    'incident/incident_photo/documentation'
                );

                // Simpan path-nya saja (String), bukan objek filenya
                $this->saved_photos[] = $path;
            }

            // 3. PENTING: Kosongkan incident_photo agar objek TemporaryUploadedFile hilang
            // Ini yang mencegah error "Serialization of ... not allowed"
            $this->incident_photo = [];
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->incident_photo = [];
            throw $e;
        }
    }
    // Mengambil detail bagian tubuh berdasarkan kategori yang dipilih
    #[Computed]
    public function detailsBodyPart()
    {
        if (!$this->selectedBodyPartCategory) return [];

        return BodyPart::where('category', $this->selectedBodyPartCategory)->get();
    }

    // Fungsi helper untuk menghapus badge
    public function removeBodyPart($id)
    {
        $this->selectedBodyParts = array_values(array_diff($this->selectedBodyParts, [$id]));
    }

    public function updatedSupportingDocuments()
    {
        try {
            // 1. Validasi setiap dokumen (PDF, DOC, DOCX, XLS, XLSX)
            $this->validateOnly('supporting_documents.*', [
                'supporting_documents.*' => 'file|mimes:pdf,doc,docx|max:5120', // Max 5MB per file
            ], [
                'supporting_documents.*.file'  => 'Input harus berupa file valid.',
                'supporting_documents.*.mimes' => 'Format file harus PDF, Word',
                'supporting_documents.*.max'   => 'Ukuran file dokumen maksimal 5MB.',
            ]);

            // 2. Bersihkan file lama dari storage jika validasi berhasil
            if (!empty($this->supporting_documents_paths)) {
                foreach ($this->supporting_documents_paths as $oldPath) {
                    FileHelper::deleteFile($oldPath);
                }
            }

            // 3. Reset array path
            $this->supporting_documents_paths = [];

            // 4. Proses penyimpanan file baru
            foreach ($this->supporting_documents as $file) {
                // FileHelper::compressAndStore akan menyimpan file asli jika bukan gambar
                $this->supporting_documents_paths[] = FileHelper::compressAndStore(
                    $file,
                    'incident/supporting_documents/documentation'
                );
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 5. AUTO-CLEAR: Jika ada file yang tidak sesuai format (misal user upload .exe atau .zip)
            // Kita reset agar state di UI kembali bersih
            $this->supporting_documents = [];

            throw $e;
        }
    }

    public function edit($likelihoodId, $consequenceId)
    {
        $this->likelihood_id = $likelihoodId;
        $this->consequence_id = $consequenceId;

        $this->selectedLikelihoodId = $likelihoodId;
        $this->selectedConsequenceId = $consequenceId;

        $this->loadRiskAssessment();
    }

    public function updatedConsequenceId()
    {
        $this->loadRiskAssessment();
        if (!in_array($this->consequence_id, [3, 4, 5])) {
            $this->penerimaan_komentar_ktt_id = null;
            $this->penerimaan_komentar_ktt = '';

            // Pastikan array searchName dibersihkan agar UI Select2 sinkron
            if (isset($this->searchNamePenerimaan['ktt'])) {
                $this->searchNamePenerimaan['ktt'] = '';
            }

            // Beritahu JS untuk menghancurkan instance editor (opsional tapi bagus untuk memori)
            $this->dispatch('reset-all-editors');
        } else {
            // Jika berubah ke 3, 4, atau 5, beri sinyal kecil untuk re-init jika diperlukan
            // Livewire v4 biasanya menangani ini lewat x-data init, tapi dispatch membantu jika ada delay render
            $this->dispatch('refresh-ktt-editor');
        }
    }

    public function updatedLikelihoodId()
    {
        $this->loadRiskAssessment();
    }
    protected function loadRiskAssessment(): void
    {
        if (!$this->likelihood_id || !$this->consequence_id) {
            $this->RiskAssessment = null;
            return;
        }

        $cell = RiskMatrixCell::where('likelihood_id', $this->likelihood_id)
            ->where('risk_consequence_id', $this->consequence_id)
            ->first();

        if (!$cell) {
            $this->RiskAssessment = null;
            return;
        }

        $matrix = RiskAssessmentMatrix::where('risk_matrix_cell_id', $cell->id)->first();

        $this->RiskAssessment = $matrix
            ? RiskAssessment::find($matrix->risk_assessment_id)
            : null;
    }
    public function getExistingCategoryProperty()
    {
        // Definisikan urutan kategori secara manual
        $order = "'Kepala', 'Tubuh Atas', 'Batang Tubuh', 'Tubuh Bawah', 'Lainnya'";

        return BodyPart::query()
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderByRaw("FIELD(category, $order)")
            ->get();
    }
    public function getHasSubTypesProperty()
    {
        if (!$this->event_type_id) {
            return false;
        }

        // Cek apakah ada anak (sub-tipe) untuk tipe yang dipilih
        return EventSubType::where('event_type_id', $this->event_type_id)->exists();
    }


    #[Computed]
    public function isInjury()
    {
        if (!$this->event_type_id) {
            return false;
        }

        $type = EventType::find($this->event_type_id);

        // Menggunakan str_contains atau strtolower untuk keamanan ekstra
        return $type && str_contains(strtolower($type->event_type_name), 'injury');
    }
    #[Computed]
    public function keterlibatanOptions()
    {
        return [
            'saksi'         => 'Saksi',
            'korban_cedera' => 'Korban Cedera',
            'kontraktor'    => 'Kontraktor',
            'operator'      => 'Operator',
            'lainnya'       => 'Lainnya',
        ];
    }
    public function render()
    {
        return view('livewire.incident.create', [
            'Department'   => Department::all(),
            'Contractors'  => Contractor::all(),
            'likelihoodss' => Likelihood::orderByDesc('level')->get(),
            'consequencess' => RiskConsequence::orderBy('level')->get(),
            'eventTypes' => EventType::onlyIncidents()->get(),
            'eventSubTypes' => EventSubType::where('event_type_id', $this->event_type_id)->get(),
            'ktas' => UnsafeCondition::latest()->get(),
            'ttas' => UnsafeAct::latest()->get(),
            // 'detailsBodyPart' => BodyPart::searchCategory($this->selectedBodyPartCategory)->orderBy('name')->get()
        ]);
    }

    // Involved Personnel

    public function addDirectlyInvolvedRow()
    {
        $this->directly_involved[] = [
            'employee_id' => null,
            'employee_name' => '',
            'employee_nik' => '',
            'dept_cont' => '',
            'jabatan' => '',
            'roster' => '',
            'sift' => '',
            'keterlibatan' => '',
            'pengalaman_kerja' => '',
        ];

        // Inisialisasi state pembantu untuk index baru
        $newIndex = count($this->directly_involved) - 1;
        $this->searchKorban[$newIndex] = '';
        $this->show_employee_dropdown[$newIndex] = false;
        $this->saveToSession();
    }

    public function removeDirectlyInvolvedRow($index)
    {
        unset($this->directly_involved[$index]);
        unset($this->searchKorban[$index]);
        unset($this->show_employee_dropdown[$index]);

        // Reset index agar berurutan kembali (penting untuk kelancaran array PHP)
        $this->directly_involved = array_values($this->directly_involved);
        $this->searchKorban = array_values($this->searchKorban);
        $this->show_employee_dropdown = array_values($this->show_employee_dropdown);
    }


    // Fungsi pencarian otomatis saat user mengetik
    public function updatedSearchKorban($value, $index)
    {
        // Ambil index dari string "searchKorban.0"
        $idx = explode('.', $index)[0];

        if (strlen($value) >= 2) {
            // Ganti dengan logic pencarian database Anda
            $this->involved_personnel_options = User::where('name', 'like', "%{$value}%")
                ->limit(5)
                ->get();
            $this->show_employee_dropdown[$idx] = true;
        } else {
            $this->show_employee_dropdown[$idx] = false;
        }
    }




    public function save()
    {
        // 1. Jalankan Validasi Global (Hanya akan memvalidasi field yang ada di rules)
        $this->validate();

        try {
            $result = DB::transaction(function () {
                $data = $this->prepareArrayData();

                // A. Simpan Header Utama (Incident Report)
                // Catatan: scat_analysis kosong karena diinput saat Update
                $report = IncidentReport::create(array_merge(
                    $data['header'],
                    ['scat_analysis' => null]
                ));

                // A2. Update Report Number secara aman dari Race Condition menggunakan Auto-Increment ID
                $report->update([
                    'report_number' => 'INC-' . now()->format('Ymd') . '-' . str_pad($report->id, 3, '0', STR_PAD_LEFT)
                ]);


                // 2. Baru proses file foto jika ada
                foreach ($this->saved_photos as $vPath) {
                    $report->attachments()->create([
                        'file_path' => $vPath,
                        'file_type' => 'incident_photo',
                        'file_name' => basename($vPath),
                    ]);
                }

                $this->reset(['saved_photos']);
                $this->reset(['incident_photo']);
                // B. Simpan Risk Assessment
                $report->risk()->create($data['risk_assessment']);

                // C. Simpan Detail Dampak (Injury vs Damage)
                $impact = $report->impact()->create([
                    'is_injury'     => $data['impact_details']['is_injury'],
                    'damage_detail' => !$data['impact_details']['is_injury'] ? $data['impact_details']['damage_data']['detail'] : null,
                ]);
                if ($data['impact_details']['is_injury'] && !empty($this->selectedBodyParts)) {
                    // Gunakan sync() untuk menyimpan array ID ke tabel pivot
                    $impact->bodyParts()->sync($this->selectedBodyParts);
                }

                // D. Simpan Personel Terlibat (Hanya Part 2)
                $report->involvedPersons()->createMany($data['pihak_terlibat']);
                // Part 3-9 dilewati karena diisi pada tahap Investigasi/Update
                return $report;
            });
            // Hapus Session draft
            session()->forget('incident_create_data');
            $this->reset();
            // Feedback Berhasil
            $this->dispatch('alert', [
                'text' => "Laporan " . $result->report_number . " berhasil dibuat!",
                'duration' => 5000,
                'destination' => '/incident/show/' . $result->id,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]);
            // [START] Logika Baru: Notifikasi ke Semua Moderator
            // Dapatkan semua ID pengguna moderator yang relevan
            // Dapatkan semua ID pengguna moderator yang relevan
            $moderatorIds = ModeratorAssignment::where('event_type_id', $result->event_type_id)
                ->where(function ($query) use ($result) {
                    // Moderator ditugaskan untuk Event Type ini,
                    // DAN penugasan tersebut harus berlaku (cocok dengan laporan)
                    // Kriteria 1: Penugasan bersifat umum (department_id dan contractor_id di assignment adalah NULL)
                    $query->whereNull('department_id')
                        ->whereNull('contractor_id');
                    // Kriteria 2: Penugasan spesifik untuk Department
                    if ($result->department_id) {
                        $query->orWhere('department_id', $result->department_id);
                    }
                    // Kriteria 3: Penugasan spesifik untuk Contractor
                    if ($result->contractor_id) {
                        $query->orWhere('contractor_id', $result->contractor_id);
                    }
                })
                ->distinct('user_id')
                ->pluck('user_id');
            $reporterName = 'Tidak Diketahui';
            if ($result->pelapor_id) {
                // Jika ada ID pelapor, ambil dari relasi User
                // Asumsi relasi User di model Hazard bernama 'pelapor'.
                // Menggunakan optional chaining (?->) untuk keamanan jika relasi belum dimuat.
                $reporterName = $result->reporter?->name ?? 'User Terdaftar';
            } else {
                // Jika tidak ada ID pelapor, ambil dari input manual
                $reporterName = $result->manualPelaporName ?? 'Anonim';
            }
            $locationName = 'N/A';
            if ($result->department_id && $result->department) {
                // Jika Department ada, gunakan namanya
                $locationName = $result->department->department_name;
            } elseif ($result->contractor_id && $result->contractor) {
                // Jika Department NULL/kosong, dan Contractor ada, gunakan namanya
                // Asumsi: Nama kolom di model Department adalah 'department_name'
                // dan nama kolom di model Contractor adalah 'name' (sesuaikan jika berbeda)
                $locationName = $result->contractor->contractor_name;
            }
            // Kirim email ke setiap moderator (Berjalan di background menggunakan defer Laravel)
            defer(function () use ($moderatorIds, $result, $reporterName, $locationName) {
                foreach ($moderatorIds as $moderatorId) {
                    MailHelper::sendToUserId(
                        $moderatorId,
                        'Notifikasi Laporan Insiden',
                        'emails.notification',
                        [
                            'subject'       => 'Laporan Insiden Baru',
                            'title'         => 'Laporan Notifikasi Insiden',
                            'messageText'   => "Telah dibuat laporan Insiden baru.\nSilakan lakukan pemeriksaan.",
                            'additionalInfo' => "Nomor Laporan: {$result->report_number}\nNama Pelapor : {$reporterName}\nLokasi Penugasan: {$locationName}\nStatus: {$result->status}",
                            'actionUrl'     => route('incident-detail', $result->id)
                        ]
                    );
                }
            });
            // [END] Logika Baru: Notifikasi ke Semua Moderator


            return $this->redirect(route('incident-detail', $result->id), navigate: true);
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan Incident Report SENTRY: ' . $e->getMessage());
            $this->dispatch('alert', [
                'text' => "Gagal menyimpan: " . $e->getMessage(),
                'duration' => 7000,
                'backgroundColor' => "background: #f44336;",
            ]);
        }
    }


    protected function prepareArrayData()
    {
        // Logika Generate Report Number Sementara (Mencegah Race Condition/Duplikat)
        // Nomor asli akan digenerate setelah data masuk ke database dan ID didapatkan
        $reportNumber = uniqid('TMP-INC-');

        return [
            // PART 1: INFORMASI DASAR
            'header' => [
                'report_number'     => $reportNumber,
                'title'             => $this->title,
                'event_type_id'     => $this->event_type_id,
                'event_sub_type_id' => $this->event_sub_type_id,
                'potential_lti'     => $this->potential_lti,
                'tasks'             => $this->tasks,
                'date_time'         => $this->date_time,
                'location_id'       => $this->location_id,
                'location_specific' => $this->location_specific,
                'contract_area_name' => $this->contract_area_name,
                'env_classification' => $this->env_classification,
                'department_id'     => $this->department_id,
                'contractor_id'     => $this->contractor_id,
                'penanggungJawab'   => $this->penanggungJawab,
                'pelapor_id'        => $this->pelapor_id,
                'user_auth'         => Auth::user()->id,
                'manual_pelapor'    => $this->manualPelaporName,
                'description'       => $this->description,
                'emergency_action'  => $this->emergency_action,
                // Null-kan field investigasi untuk pembuatan awal
                'key_learning'      => null,
            ],

            // RISK MATRIX
            'risk_assessment' => [
                'likelihood_id'  => $this->likelihood_id,
                'consequence_id' => $this->consequence_id,
                'rating_name'    => $this->RiskAssessment?->name,
                'deadline'       => $this->RiskAssessment?->notes,
            ],

            // IMPACT DETAILS
            'impact_details' => [
                'is_injury'   => $this->isInjury,
                'injury_data' => $this->isInjury ? [
                    'part_id' => $this->selectedBodyParts,
                ] : null,
                'damage_data' => !$this->isInjury ? [
                    'detail'  => $this->damage_detail,
                ] : null,
            ],
            // PART 2: PERSONEL TERLIBAT
            'pihak_terlibat' => collect($this->directly_involved)->map(function ($person) {
                return [
                    'employee_id'      => $person['employee_id'] ?? null,
                    'employee_name'    => $person['employee_name'],
                    'employee_nik'     => $person['employee_nik'],
                    'dept_cont'        => $person['dept_cont'],
                    'jabatan'          => $person['jabatan'],
                    'roster'           => $person['roster'],
                    'shift'            => $person['sift'], // tetap menggunakan 'sift' sesuai model
                    'keterlibatan'     => $person['keterlibatan'],
                    'pengalaman_kerja' => $person['pengalaman_kerja'],
                ];
            })->toArray(),
        ];
    }
    private function goToStepByField($field)
    {
        if (in_array($field, [
            'title',
            'event_type_id',
            'event_sub_type_id',
            'date_time',
            'location_id',
            'location_specific',
            'contract_area_name',
            'env_classification',
            'department_id',
            'contractor_id',
            'penanggungJawab',
            'pelapor_id',
            'manualPelaporName',
            'consequence_id',
            'likelihood_id',
            'description',
            'emergency_action',
            'selectedBodyPartCategory',
            'selectedBodyParts',
            'damage_detail'
        ]) || str_starts_with($field, 'selectedBodyParts')) {
            $this->currentStep = 1;
        } elseif (str_starts_with($field, 'directly_involved')) {
            $this->currentStep = 2;
        }

        $this->dispatch('scroll-to-top');
    }

    public function isFieldInStep($step, $errorFields)
    {
        $fields = array_keys($errorFields);

        foreach ($fields as $field) {
            if ($step == 1) {
                $step1Fields = [
                    'title',
                    'event_type_id',
                    'event_sub_type_id',
                    'date_time',
                    'location_id',
                    'tasks',
                    'potential_lti',
                    'location_specific',
                    'contract_area_name',
                    'env_classification',
                    'department_id',
                    'contractor_id',
                    'penanggungJawab',
                    'pelapor_id',
                    'manualPelaporName',
                    'consequence_id',
                    'likelihood_id',
                    'description',
                    'emergency_action',
                    'selectedBodyPartCategory',
                    'selectedBodyPart',
                    'damage_detail'
                ];
                if (in_array($field, $step1Fields)) return true;
            }

            if ($step == 2) {
                if (str_starts_with($field, 'directly_involved')) return true;
            }
        }

        return false;
    }
    #[Computed]
    public function environmentalIncidentOptions()
    {
        return [
            'Tidak Signifikan' => __('Tidak Signifikan'),
            'Ringan'            => __('Ringan'),
            'Sedang'           => __('Sedang'),
            'Berat'            => __('Berat'),
            'Bencana'          => __('Bencana'),
        ];
    }
    #[Computed]
    public function contractAreaOptions()
    {
        return [
            'PT. MSM'  => 'PT. MSM',
            'PT. TTN'  => 'PT. TTN',
            'Off Site' => __('Off Site'),
        ];
    }
    // Di dalam class Livewire Anda

    #[Computed]
    public function isEnvironmentType()
    {
        // Cari nama event type berdasarkan ID yang sedang dipilih user
        $selectedType = EventType::where('id', $this->event_type_id)->first();

        // Pastikan pengecekan string sesuai dengan data di database Anda (misal: 'Lingkungan' atau 'Environment')
        return $selectedType && ($selectedType['event_type_name'] === 'Lingkungan' || $selectedType['event_type_name'] === 'Environment');
    }
}
