<?php

namespace App\Livewire\Incident;

use App\Helpers\FileHelper;
use App\Helpers\MailHelper;
use App\Mail\IncidentAlertMail;
use App\Models\BodyPart;
use App\Models\Contractor;
use App\Models\Department;
use App\Models\EventSubType;
use App\Models\EventType;
use App\Models\Hazard;
use App\Models\IncidentAttachment;
use App\Models\IncidentReport;
use App\Models\Likelihood;
use App\Models\RiskAssessment;
use App\Models\RiskAssessmentMatrix;
use App\Models\RiskConsequence;
use App\Models\RiskMatrixCell;
use App\Models\ScatOption;
use App\Models\UnsafeAct;
use App\Models\UnsafeCondition;
use App\Models\User;
use App\Traits\WithDeptContSelection;
use App\Traits\WithSearchLocation;
use App\Traits\WithSearchPelapor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Update extends Component
{
    use WithFileUploads, WithPagination, WithDeptContSelection, WithSearchLocation, WithSearchPelapor;
    // Di dalam class IncidentEdit extends Component
    // --- IDENTITAS & STATE ---
    public $incidentId;
    public $report_number; // Untuk ditampilkan di header
    public $currentStep = 1;

    // --- PART 1: DATA DASAR & LOKASI ---
    public $event_type_id;
    public $event_sub_type_id;
    public $date_time;
    public $location_id;
    public $location_specific;
    public $tasks, $potential_lti;
    // --- PART 1: DEPT & PIC ---
    public $deptCont = 'dept'; // Default selector
    public $department_id;
    public $contractor_id;

    public $penanggungJawab; // User ID untuk PIC
    // --- PART 1: PELAPOR (ADVANCED SEARCH) ---
    public $pelapor_id;

    // --- PART 1: RISK MATRIX ---
    public $consequence_id;
    public $likelihood_id;
    public $RiskAssessment = null; // Menyimpan objek RiskMatrixCell yang terpilih
    public $selectedLikelihoodId, $selectedConsequenceId;
    public $risk_consequence;

    // --- PART 1: NARASI & IMPACT ---
    public $description;
    public $emergency_action;
    public $isInjury = false; // Toggle status Cedera Manusia vs Kerusakan Alat

    public $selectedBodyParts = []; // Pastikan ini array
    public $selectedBodyPartCategory = null;
    public $damage_detail;

    // --- KOLEKSI DATA (FOR DROPDOWNS) ---
    public $status, $title;
    public $locations = [];
    public $departments = [];
    public $contractors = [];
    public $penanggungJawabOptions = [];
    public $consequences = [];  // Untuk header table matrix
    public $likelihoods = [];   // Untuk row table matrix
    // Tambahkan property baru
    public $manualMode = []; // Array untuk menyimpan status manual per index
    public $manualEmployeeName = [];
    /**
     * Computed Property untuk Sub-Tipe Insiden
     * Otomatis update saat event_type_id berubah
     */
    public $why_analysis = [];
    public $whyCount = 1;
    public $env_classification, $contract_area_name;
    public $directly_involved = []; // Menampung data baris personel
    public $searchKorban = [];      // Menampung input pencarian per baris
    public $show_employee_dropdown = []; // Status dropdown per baris
    public $involved_personnel_options = []; // Hasil pencarian DB

    // Data Tim Investigasi
    public $pemimpin = [];
    public $facilitator = [];
    public $anggota = [];
    public $rating_name;
    public $manualModePetugas = [];
    public $manualNamePetugas = [];

    // State untuk Pencarian/Dropdown
    public $searchQuery = []; // Struktur: [$index][$type] => 'string'
    public $showDropdownPartisipan = [];
    public $activeType = null;
    public $activeIndex = null;
    public $options = []; // Hasil pencarian User
    public $peepoFactors = [
        'orang' => 'Orang',
        'peralatan' => 'Peralatan',
        'lingkungan' => 'Lingkungan',
        'prosedur' => 'Prosedur',
        'organisasi' => 'Organisasi'
    ];
    public $peepo = [
        'orang' => [
            [
                'temuan' => '',
                'deskripsi' => ''
            ]
        ],

        'peralatan' => [
            [
                'temuan' => '',
                'deskripsi' => ''
            ]
        ],

        'lingkungan' => [
            [
                'temuan' => '',
                'deskripsi' => ''
            ]
        ],

        'prosedur' => [
            [
                'temuan' => '',
                'deskripsi' => ''
            ]
        ],

        'organisasi' => [
            [
                'temuan' => '',
                'deskripsi' => ''
            ]
        ],
    ];
    public $searchNamePenerimaan = [
        'kontraktor' => '',
        'internal' => '',
        'ohs' => '',
        'ktt' => '',
    ];
    public $activeTypePenerimaan = '';
    public $existing_visual_evidence = [];
    public $existing_saved_photos = [];
    public $existing_supporting_documents = [];

    public $unsafe_conditions = [];
    public $unsafe_acts = [];
    public $personal_factors = [];
    public $job_factors = [];
    public $control_system_factors = [];
    // Di bagian atas Class
    public $visual_evidence = [];
    public $saved_photos = [];
    public $incident_photo = [];
    public $supporting_documents = [];
    public $saved_photos_paths = []; // Ubah dari string ke array
    public $visual_evidence_paths = []; // Ubah dari string ke array
    public $supporting_documents_paths = [];
    public $corrective_actions = [];
    public $searchPetugas = [];
    public $showDropdownPetugas = [];
    public $pelaporsAct = [];
    public $showPenerimaanKomentarContractorDropdown = false;
    public $showPenerimaanKomentarInternalDropdown = false;
    public $showPenerimaanKomentarOhsDropdown = false;
    public $showPenerimaanKomentarKttDropdown = false;
    public $penerimaan_komentar_contractor_id;
    public $penerimaan_komentar_internal_id;
    public $penerimaan_komentar_ohs_id;
    public $penerimaan_komentar_ktt_id;
    public $penerimaan_komentar_ktt;
    public $incident;
    // Properti untuk teks editor (CKEditor)
    public $penerimaan_komentar_contractor;
    public $penerimaan_komentar_internal;
    public $penerimaan_komentar_ohs;
    public $key_learning;
    public $current_lock_version;
    public $manualName = [];
    // TAMBAHKAN INI:
    public $activeIndexPenerimaan = null;
    #[Computed]
    public $name_ktt;
    public function isInjury()
    {
        if (!$this->event_type_id) {
            return false;
        }

        $type = EventType::find($this->event_type_id);

        // Menggunakan str_contains atau strtolower untuk keamanan ekstra
        return $type && str_contains(strtolower($type->event_type_name), 'injury');
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

    public function rules()
    {
        $hasVisual = (count($this->existing_visual_evidence) > 0) || (is_array($this->visual_evidence) && count($this->visual_evidence) > 0);

        // Cek apakah ada Supporting Documents (di DB OR sedang di-upload)
        $hasDocument = (count($this->existing_supporting_documents) > 0) || (is_array($this->supporting_documents) && count($this->supporting_documents) > 0);
        $rules = [
            // PART 1
            'title' => 'required|string|max:255',
            'event_type_id' => 'required|exists:event_types,id',

            // Hanya required jika event type ini memang punya sub-types
            'event_sub_type_id' => $this->hasSubTypes ? 'required|exists:event_sub_types,id' : 'nullable',

            'potential_lti' => 'required|in:Yes,No',
            'tasks' => 'required|string|min:1',
            'description' => 'required|string',
            'location_id' => 'required|exists:locations,id',
            'location_specific' => 'required|string',
            'contract_area_name' => 'required|string',

            // Hanya required jika tipe kejadian adalah Environment/Lingkungan
            'env_classification' => $this->isEnvironmentType ? 'required|string' : 'nullable',

            'date_time' => 'required|date',

            // Pelapor
            'pelapor_id' => 'required_without:manualPelaporName|nullable|exists:users,id',
            'manualPelaporName' => 'required_without:pelapor_id|nullable|string',

            'deptCont' => 'required|in:dept,cont',
            'department_id' => $this->deptCont === 'dept' ? 'required|exists:departments,id' : 'nullable',
            'contractor_id' => $this->deptCont === 'cont' ? 'required|exists:contractors,id' : 'nullable',

            'likelihood_id' => 'required',
            'consequence_id' => 'required',
            'emergency_action' => 'required|string',
            'penanggungJawab' => 'required|exists:users,id',

            // LOGIKA KONDISIONAL Injury vs Damage
            'selectedBodyPartCategory' => $this->isInjury() ? 'required' : 'nullable',
            'selectedBodyParts' => $this->isInjury() ? 'required' : 'nullable',
            'selectedBodyParts.*' => 'exists:body_parts,id',
            'damage_detail' => !$this->isInjury() ? 'required|string' : 'nullable',
            // Part 2
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
            // PART 3: Tim Investigasi
            'pemimpin' => 'required|array|min:1',
            'pemimpin.*.user_id' => 'nullable|exists:users,id',
            'pemimpin.*.name' => 'required|string',
            'pemimpin.*.dept'    => 'required|string',
            'pemimpin.*.jabatan' => 'required|string',

            'facilitator' => 'required|array|min:1',
            'facilitator.*.user_id' => 'nullable|exists:users,id',
            'facilitator.*.name' => 'required|string',
            'facilitator.*.dept'    => 'required|string',
            'facilitator.*.jabatan' => 'required|string',

            'anggota' => 'required|array|min:1',
            'anggota.*.user_id' => 'nullable|exists:users,id',
            'anggota.*.name' => 'required|string',
            'anggota.*.dept'    => 'required|string',
            'anggota.*.jabatan' => 'required|string',
            // PART 4: PEEPO (Analisis Faktor)
            'peepo.orang' => 'required|array|min:1',
            'peepo.orang.*.temuan' => 'required|string|min:3',
            'peepo.orang.*.deskripsi' => 'required|string|min:5',

            'peepo.peralatan' => 'required|array|min:1',
            'peepo.peralatan.*.temuan' => 'required|string|min:3',
            'peepo.peralatan.*.deskripsi' => 'required|string|min:5',

            'peepo.lingkungan' => 'required|array|min:1',
            'peepo.lingkungan.*.temuan' => 'required|string|min:3',
            'peepo.lingkungan.*.deskripsi' => 'required|string|min:5',

            'peepo.prosedur' => 'required|array|min:1',
            'peepo.prosedur.*.temuan' => 'required|string|min:3',
            'peepo.prosedur.*.deskripsi' => 'required|string|min:5',

            'peepo.organisasi' => 'required|array|min:1',
            'peepo.organisasi.*.temuan' => 'required|string|min:3',
            'peepo.organisasi.*.deskripsi' => 'required|string|min:5',
            // PART 5: Timeline & Why Analysis
            'why_analysis' => 'required|array',
            // Part 6
            // Validasi Kondisi Tidak Aman
            'unsafe_conditions.*.item' => 'required',
            'unsafe_conditions.*.description' => 'required|string|min:5',

            // Validasi Perilaku Tidak Aman
            'unsafe_acts.*.item' => 'required',
            'unsafe_acts.*.description' => 'required|string|min:5',

            // Validasi Faktor Pribadi
            'personal_factors.*.item' => 'required',
            'personal_factors.*.description' => 'required|string|min:5',

            // Validasi Faktor Pekerjaan
            'job_factors.*.item' => 'required',
            'job_factors.*.description' => 'required|string|min:5',

            // Validasi Kelemahan Sistem Kontrol
            'control_system_factors.*.item' => 'required',
            'control_system_factors.*.description' => 'required|string|min:5',
            // Part 7
            'visual_evidence' => !$hasDocument ? 'required|array|min:1' : 'nullable|array',
            // Validasi tiap file di dalam array (Ukuran dan Tipe)
            'visual_evidence.*' => 'image|max:2048', // Maks 2MB per foto

            'supporting_documents' => !$hasVisual ? 'required|array|min:1' : 'nullable|array',
            'supporting_documents.*' => 'mimes:pdf,doc,docx|max:5120',
            // Validasi Tabel Tindakan Perbaikan (Array Dinamis)
            'corrective_actions.*.action_description' => 'required|string|min:10',
            'corrective_actions.*.control_hierarchy' => 'required|in:Eliminasi,Substitusi,Engineering,Administrasi,APD',
            'corrective_actions.*.pic_user_id' => [
                'nullable',
                'exists:users,id',
                'required_without:corrective_actions.*.name'
            ],

            'corrective_actions.*.name' => [
                'nullable',
                'string',
                'required_without:corrective_actions.*.pic_user_id'
            ],
            'corrective_actions.*.due_date' => 'required|date|after_or_equal:date_time',
            'corrective_actions.*.actual_completion_date' => [
                'nullable',
                'date',
                // 'index' akan otomatis dipetakan oleh Laravel/Livewire untuk baris yang sama
                'after_or_equal:corrective_actions.*.due_date'
            ],
            // // Part 8
            'key_learning' => 'required|string|min:10',
            // Part 9

            'penerimaan_komentar_internal_id'   => 'required|exists:users,id',
            'penerimaan_komentar_ohs_id'        => 'required|exists:users,id',
            'penerimaan_komentar_contractor'    => 'required|min:11',
            'penerimaan_komentar_internal'      => 'required|min:11',
            'penerimaan_komentar_ohs'           => 'required|min:11',


        ];

        // Tambahkan Logika KTT di sini agar terbaca secara global
        if (in_array($this->rating_name, ['Sedang', 'Tinggi', 'Ekstrem'])) {
            $rules['penerimaan_komentar_ktt_id'] = 'required|exists:users,id';
            $rules['penerimaan_komentar_ktt']    = 'required|min:11';
        }
        if ($this->contractor_id) {
            $rules['penerimaan_komentar_contractor_id'] = 'required|exists:users,id';
            $rules['penerimaan_komentar_contractor']    = 'required|min:11';
        }


        // PERBAIKAN DI SINI:
        // Gunakan $rules, bukan $attributes.
        // Dan pastikan key-nya sesuai dengan data binding Anda.
        foreach (range(1, $this->whyCount) as $i) {
            $rules["why_analysis.why{$i}"] = 'required|string|min:3';
        }

        return $rules;
    }
    protected function validationAttributes()
    {
        $attributes = [
            'title' => __('Judul Laporan'),
            'pelapor_id'        => __('Nama Pelapor'),
            'manualPelaporName' => __('Nama Pelapor Manual'),
            'event_type_id'     => __('Tipe Kejadian'),
            'event_sub_type_id' => __('Sub Tipe Kejadian'),
            'potential_lti'     => __('Potensi LTI/Fatality'),
            'tasks'             => __('Tugas/Tindakan Cepat'),
            'description'       => __('Deskripsi Kejadian'),
            'location_id'       => __('Lokasi Utama'),
            'location_specific' => __('Detail Lokasi Spesifik'),
            'contract_area_name' => __('Area Kontrak Karya'),
            'env_classification' => __('Klasifikasi Lingkungan'),
            'date_time'         => __('Tanggal dan Waktu'),
            'department_id'     => __('Departemen'),
            'contractor_id'     => __('Perusahaan Kontraktor'),
            'deptCont'          => __('Pihak Terlibat'),
            'penanggungJawab'   => __('PIC / Penanggung Jawab'),
            'likelihood_id'     => __('Kemungkinan (Likelihood)'),
            'consequence_id'    => __('Konsekuensi (Consequence)'),
            'emergency_action'  => __('Tindakan Darurat'),
            'selectedBodyPartCategory' => __('Kategori Bagian Tubuh'),
            'selectedBodyParts'         => __('Detail Bagian Tubuh'),
            'selectedBodyParts.*' => __('Detail Bagian Tubuh'),
            'damage_detail'            => __('Detail Kerusakan Alat / Lingkungan'),

            'directly_involved.*.employee_name' => __('Nama Personel'),
            'directly_involved.*.employee_nik'  => __('NIK/ID'),
            'directly_involved.*.dept_cont'     => __('Departemen/Perusahaan'),
            'directly_involved.*.jabatan'       => __('Jabatan'),
            'directly_involved.*.roster'        => __('Roster'),
            'directly_involved.*.sift'          => __('Shift'),
            'directly_involved.*.keterlibatan'  => __('Jenis Keterlibatan'),
            'directly_involved.*.pengalaman_kerja' => __('Pengalaman Kerja'),

            'pemimpin.*.name' => __('Nama Pemimpin'),
            'facilitator.*.name' => __('Nama Facilitator'),
            'anggota.*.name' => __('Nama Anggota'),

            'visual_evidence.*' => __('Bukti Visual'),
            'supporting_documents.*' => __('Dokumen Pendukung'),

            'corrective_actions.*.action_description' => __('Rencana Perbaikan'),
            'corrective_actions.*.control_hierarchy'  => __('Hirarki Kontrol'),
            'corrective_actions.*.name'               => __('Nama PIC'),
            'corrective_actions.*.pic_user_id'        => __('PIC (Daftar User)'),
            'corrective_actions.*.due_date'           => __('Batas Waktu'),
            'corrective_actions.*.actual_completion_date' => __('Tanggal Selesai Aktual'),

            'key_learning' => __('Kunci Pembelajaran'),
        ];

        foreach ($this->peepoFactors as $key => $label) {

            $attributes["peepo.$key.*.temuan"] =
                __('Temuan Faktor') . ' ' . __($label);

            $attributes["peepo.$key.*.deskripsi"] =
                __('Deskripsi Faktor') . ' ' . __($label);
        }

        foreach (['unsafe_conditions', 'unsafe_acts', 'personal_factors', 'job_factors', 'control_system_factors'] as $key) {
            foreach ($this->$key as $index => $row) {
                $rowNum = $index + 1;
                $label = str_replace('_', ' ', ucwords($key, '_'));
                $attributes["$key.$index.item"] = __("$label Baris $rowNum");
                $attributes["$key.$index.description"] = __("Deskripsi $label Baris $rowNum");
            }
        }

        return $attributes;
    }

    protected function messages()
    {
        return [
            'required' => __(':attribute wajib diisi.'),
            'exists'   => __('Pilihan :attribute tidak valid.'),
            'min'      => __(':attribute minimal harus :min karakter.'),
            'date'     => __('Format tanggal :attribute tidak sesuai.'),
            'after_or_equal' => __(':attribute tidak boleh sebelum :date.'),

            'supporting_documents.*.mimes' => __('Hanya file PDF dan Word yang diperbolehkan.'),
            'supporting_documents.*.max'   => __('Ukuran file dokumen tidak boleh lebih dari 5MB.'),
            'visual_evidence.required'     => __('Bukti visual wajib dilampirkan.'),
            'visual_evidence.*.image'      => __('File harus berupa gambar (JPG, PNG, WebP).'),
            'visual_evidence.*.max'        => __('Ukuran foto maksimal 2MB.'),

            'corrective_actions.*.name.required_without'        => __('Nama PIC wajib diisi jika tidak memilih dari daftar.'),
            'corrective_actions.*.pic_user_id.required_without' => __('Silakan pilih petugas atau masukkan nama manual.'),
            'corrective_actions.*.due_date.after_or_equal'      => __('Tanggal batas waktu tidak boleh sebelum tanggal kejadian.'),
            'corrective_actions.*.actual_completion_date.after_or_equal' => __('Tanggal selesai tidak boleh sebelum batas waktu.'),

            'key_learning.required' => __('Kunci pembelajaran wajib diisi sebagai bahan evaluasi.'),
            'department_id.required_without' => __('Silakan pilih Departemen atau Kontraktor.'),
            'contractor_id.required_without' => __('Pilih Kontraktor atau Department terkait.'),
        ];
    }
    public function updated($propertyName)
    {
        // 1. Logika Bisnis: Update otomatis Status/Progress
        // Jalankan ini DI AWAL agar perubahan properti langsung tercermin di class
        if (str_contains($propertyName, 'corrective_actions')) {
            $parts = explode('.', $propertyName);

            if (isset($parts[1]) && isset($parts[2]) && $parts[2] === 'actual_completion_date') {
                $index = $parts[1];

                if (!empty($this->corrective_actions[$index]['actual_completion_date'])) {
                    $this->corrective_actions[$index]['status'] = 'Selesai';
                    $this->corrective_actions[$index]['progress'] = 100;
                } else {
                    $this->corrective_actions[$index]['status'] = 'Belum Selesai';
                    $this->corrective_actions[$index]['progress'] = 0;
                }
                $this->dispatch('refresh-component');
            }
        }

        // 2. Simpan ke Session
        // Gunakan fungsi helper saveToSession yang sudah Anda buat agar kode tidak duplikat
        $this->saveToSession();

        // 3. Validasi Kondisional
        if ($propertyName === 'event_type_id') {
            $this->validateOnly('selectedBodyPartCategory');
            $this->validateOnly('selectedBodyParts');
            $this->validateOnly('damage_detail');
        }

        // 4. Validasi Standar (Real-time feedback)
        $this->validateOnly($propertyName);
    }

    protected function saveToSession()
    {
        $data = $this->all();
        $data['whyCount'] = $this->whyCount;
        // Hapus file dan properti yang tidak bisa diserialisasi
        unset(
            $data['visual_evidence'],
            $data['supporting_documents'],
            $data['visual_evidence_paths'],
            $data['supporting_documents_paths'],
            $data['incident_photo'],
            $data['saved_photos']
        );

        session()->put('incident_data', $data);
    }
    public function mount($id)
    {
        // 1. Data referensi statis
        $this->likelihoods = Likelihood::orderByDesc('level')->get();
        $this->consequences = RiskConsequence::orderBy('level')->get();
        $this->incidentId = $id;



        /**
         * 2. EAGER LOADING (Optimasi N+1)
         * Kita memuat semua relasi di awal, termasuk yang sebelumnya terlewat
         * seperti 'location', 'department', 'contractor', dan 'timelines'.
         */
        if ($this->incidentId) {
            $report = IncidentReport::with([
                'risk',
                'impact.bodyParts',
                'involvedPersons',
                'investigationTeams.user',
                'peepoAnalyses',
                'attachments',
                'correctiveActions.pic',
                'location',      // Tambahkan untuk lokasi
                'department',    // Tambahkan untuk departemen
                'contractor',    // Tambahkan untuk kontraktor
                'timelines',     // Tambahkan untuk analisis WHY
                'pmContractor',
                'pmInternal',
                'ohsHead',
                'ktt'
            ])->findOrFail($id);

            // Simpan instance ke properti agar tidak query ulang di fungsi lain
            $this->incident = $report;

            // --- DATA DASAR ---
            $this->current_lock_version = $report->lock_version;
            $this->title = $report->title;
            $this->report_number = $report->report_number;
            $this->status = $report->status;
            $this->event_type_id = $report->event_type_id;
            $this->event_sub_type_id = $report->event_sub_type_id;
            $this->tasks = $report->tasks;
            $this->potential_lti = $report->potential_lti;
            $this->date_time = $report->date_time?->format('Y-m-d\TH:i');
            $this->key_learning = $report->key_learning;
            $this->location_specific = $report->location_specific;
            $this->contract_area_name = $report->contract_area_name;
            $this->env_classification = $report->env_classification;
            $this->description = $report->description;
            $this->emergency_action = $report->emergency_action;
            $this->penanggungJawab = $report->penanggungJawab;
            $this->saved_photos = $report->attachments
                ->where('file_type', 'saved_photos')
                ->pluck('file_path')
                ->toArray();
            // --- LOKASI ---
            if ($report->location_id) {
                $this->location_id = $report->location_id;
                $this->searchLocation = $report->location?->name;
            }

            // --- DEPARTEMEN / KONTRAKTOR ---
            if ($report->department_id) {
                $this->department_id = $report->department_id;
                $this->deptCont = 'dept';
                $this->search = $report->department?->department_name;
                $this->loadInitialPenanggungJawab('department', $report->department_id);
            } elseif ($report->contractor_id) {
                $this->contractor_id = $report->contractor_id;
                $this->deptCont = 'cont';
                $this->searchContractor = $report->contractor?->contractor_name;
                $this->loadInitialPenanggungJawab('contractor', $report->contractor_id);
            }

            // --- PELAPOR ---
            $this->loadInitialPelapor($report->pelapor_id, $report->manual_pelapor_name);

            // --- RISK MATRIX ---
            if ($report->risk) {
                $this->consequence_id = $report->risk->consequence_id;
                $this->likelihood_id = $report->risk->likelihood_id;
                $this->rating_name = $report->risk->rating_name;
                $this->selectedLikelihoodId = $this->likelihood_id;
                $this->selectedConsequenceId = $this->consequence_id;
                $this->loadRiskAssessment();
            }

            // --- IMPACT & BODY PART ---
            $impact = $report->impact;
            $this->isInjury = $impact?->is_injury ?? false;

            if ($this->isInjury) {
                // 1. Ambil semua ID dari relasi pivot
                // pluck('id') mengambil semua ID, toArray() mengubahnya jadi array
                $this->selectedBodyParts = $impact->bodyParts->pluck('id')
                    ->map(fn($id) => (string)$id) // Opsional: paksa ke string agar cocok dengan value checkbox
                    ->toArray();


                // 2. Set kategori default untuk filter (diambil dari item pertama yang dipilih)
                if (!empty($this->selectedBodyParts)) {
                    $firstPart = BodyPart::find($this->selectedBodyParts[0]);
                    $this->selectedBodyPartCategory = BodyPart::find($this->selectedBodyParts[0])?->category;
                }

                $this->damage_detail = null;
            } else {
                $this->selectedBodyParts = [];
                $this->selectedBodyPartCategory = null;
                $this->damage_detail = $impact?->damage_detail;
            }

            // --- PART 2: PERSONEL TERLIBAT ---
            if ($report->involvedPersons->isNotEmpty()) {
                foreach ($report->involvedPersons as $person) {
                    $this->directly_involved[] = [
                        'id' => $person->id,
                        'employee_id' => $person->employee_id,
                        'employee_name' => $person->employee_name,
                        'employee_nik' => $person->employee_nik,
                        'dept_cont' => $person->dept_cont,
                        'jabatan' => $person->jabatan,
                        'roster' => $person->roster,
                        'sift' => $person->shift,
                        'keterlibatan' => $person->keterlibatan,
                        'pengalaman_kerja' => $person->pengalaman_kerja,
                    ];
                    $this->searchKorban[] = $person->employee_name;
                }
            } else {
                $this->addDirectlyInvolvedRow();
            }

            // --- PART 3: TIM INVESTIGASI ---
            $teams = $report->investigationTeams;
            foreach (['pemimpin', 'facilitator', 'anggota'] as $role) {
                $filtered = $teams->filter(fn($item) => stripos($item->role, $role) !== false);
                if ($filtered->isNotEmpty()) {
                    foreach ($filtered->values() as $index => $team) {
                        $this->{$role}[] = [
                            'user_id' => $team->user_id,
                            'name'    => $team->name ?? '',
                            'dept'    => $team->dept,
                            'jabatan' => $team->jabatan,
                        ];
                        $this->searchQuery[$index][$role] = $team->user->name ?? $team->name;
                    }
                } else {
                    $this->addRow($role);
                }
            }

            // --- PART 4: PEEPO ---

            // Reset default
            $this->peepo = [];

            // Ambil data peepo dari relasi
            $peepoData = $this->incident
                ->peepoAnalyses
                ->groupBy('factor_key');

            // Loop semua factor
            foreach ($this->peepoFactors as $key => $label) {

                // Jika ada data di database
                if (isset($peepoData[$key]) && $peepoData[$key]->count()) {

                    $this->peepo[$key] = $peepoData[$key]
                        ->map(function ($item) {

                            return [
                                'temuan' => $item->temuan ?? '',
                                'deskripsi' => $item->deskripsi ?? '',
                            ];
                        })
                        ->toArray();
                } else {

                    // Default 1 row kosong
                    $this->peepo[$key] = [
                        [
                            'temuan' => '',
                            'deskripsi' => '',
                        ]
                    ];
                }
            }
            // --- ANALISIS SCAT & WHY ---
            $scat = $report->scat_analysis;
            if ($scat) {
                $this->unsafe_conditions = $scat['langsung']['kondisi_tidak_aman'] ?? [];
                $this->unsafe_acts       = $scat['langsung']['perilaku_tidak_aman'] ?? [];
                $this->personal_factors  = $scat['dasar']['faktor_pribadi'] ?? [];
                $this->job_factors       = $scat['dasar']['faktor_pekerjaan'] ?? [];
                $this->control_system_factors = $scat['dasar']['sistem_kontrol'] ?? [];
            }

            // Tambahkan baris kosong jika kategori SCAT kosong
            foreach (['unsafe_conditions', 'unsafe_acts', 'personal_factors', 'job_factors', 'control_system_factors'] as $cat) {
                if (empty($this->{$cat})) $this->addRow($cat);
            }

            // Gunakan relasi timelines yang sudah di-eager load (Collection, bukan Query)
            $analysis = $report->timelines->first();
            if ($analysis && is_array($analysis->analysis_steps)) {
                $this->why_analysis = $analysis->analysis_steps;
                $this->whyCount = count($this->why_analysis) ?: 1;
            } else {
                $this->why_analysis = ['why1' => ''];
                $this->whyCount = 1;
            }

            // --- ATTACHMENTS ---
            $this->existing_visual_evidence = $report->attachments->where('file_type', 'visual')->values()->all();
            $this->existing_saved_photos = $report->attachments->where('file_type', 'incident_photo')->values()->all();
            $this->existing_supporting_documents = $report->attachments->where('file_type', 'document')->values()->all();

            // --- TINDAKAN PERBAIKAN ---
            $this->corrective_actions = $report->correctiveActions->map(function ($action, $index) {
                $this->searchPetugas[$index] = $action->pic->name ?? $action->name;
                return [
                    'id' => $action->id,
                    'action_description' => $action->action_description,
                    'control_hierarchy' => $action->hierarchy,
                    'pic_user_id' => $action->pic_user_id,
                    'name' => $action->name ?? '',
                    'due_date' => $action->due_date,
                    'actual_completion_date' => $action->actual_completion_date,
                ];
            })->toArray();
            if (empty($this->corrective_actions)) $this->addCorrectiveRow();

            // --- PART 9: REVIEW & APPROVAL ---
            if ($this->contractor_id) {
                $this->penerimaan_komentar_contractor_id = $report->pm_contractor_id;
                $this->penerimaan_komentar_contractor    = $report->pm_contractor_comment;
                $this->searchNamePenerimaan['kontraktor'] = $report->pmContractor?->name;
            }

            if ($report->pm_internal_id) {
                $this->penerimaan_komentar_internal_id   = $report->pm_internal_id;
                $this->penerimaan_komentar_internal      = $report->pm_internal_comment;
                $this->searchNamePenerimaan['internal']  = $report->pmInternal?->name;
            }

            if ($report->ohs_head_id) {
                $this->penerimaan_komentar_ohs_id        = $report->ohs_head_id;
                $this->penerimaan_komentar_ohs           = $report->ohs_head_comment;
                $this->searchNamePenerimaan['ohs']       = $report->ohsHead?->name;
            }

            if (in_array($this->rating_name, ['Sedang', 'Tinggi', 'Ekstrem'])) {
                $this->penerimaan_komentar_ktt_id    = $report->ktt_id;
                $this->penerimaan_komentar_ktt       = $report->ktt_comment;
                $this->searchNamePenerimaan['ktt']   = $report->ktt?->name;
            }
        } else {
            $this->selectedBodyParts = [];
        }
    }
    #[Computed]
    public function detailsBodyPart()
    {
        if (!$this->selectedBodyPartCategory) return [];

        return BodyPart::where('category', $this->selectedBodyPartCategory)->get();
    }

    // Fungsi helper untuk menghapus badge
    public function removeBodyPart($id)
    {
        // Filter array untuk menghapus ID yang dipilih
        $this->selectedBodyParts = array_values(array_filter($this->selectedBodyParts, function ($value) use ($id) {
            return $value != $id;
        }));

        // Opsional: Refresh validation jika perlu
        $this->validateOnly('selectedBodyParts');
    }
    public function updatedSelectedBodyPartCategory($value)
    {
        // Cukup biarkan kosong.
        // Kehadiran method ini memaksa Livewire melakukan request ke server
        // setiap kali nilai selectedBodyPartCategory berubah.
    }
    // Jika Anda ingin berpindah tab di dalam Bagian 9
    public function determineReportStatus()
    {
        // 1. REFRESH TOTAL
        // Pastikan semua relasi yang digunakan di bawah ini masuk ke dalam array fresh()
        $this->incident = $this->incident->fresh([
            'correctiveActions',
            'investigationTeams',
            'peepoAnalyses',
            'timelines'
        ]);

        if (!$this->incident) return 'Open';

        // --- Step 1-6: Investigasi ---
        $hasTeams = $this->incident->investigationTeams->isNotEmpty(); // Lebih cepat dari count() > 0

        // Gunakan collection methods (memproses di memori)
        $hasAnalysis = $this->incident->peepoAnalyses
            ->whereNotIn('temuan', ['-', '', null])
            ->isNotEmpty() ||
            $this->incident->timelines
            ->where('why_count_used', '>', 0)
            ->isNotEmpty();

        $hasScat = filled($this->incident->scat_analysis);
        $investigationComplete = $hasTeams && $hasAnalysis && $hasScat;

        // --- Step 7-8: Action Plan & Key Learning ---
        $allActions = $this->incident->correctiveActions; // Ambil collection sekali
        $totalActions = $allActions->count();
        $hasKeyLearning = filled($this->incident->key_learning);
        $actionPlanComplete = $totalActions > 0;

        // --- Step 9: Review/Approval ---
        $reviewInternalMet = filled($this->incident->pm_internal_id);
        $reviewOhsMet      = filled($this->incident->ohs_head_id);

        // LOGIKA KONTRAKTOR
        $currentContractorId = ($this->deptCont === 'cont') ? $this->contractor_id : null;
        $contractorRequirementMet = filled($currentContractorId)
            ? filled($this->incident->pm_contractor_id)
            : true;

        // LOGIKA KTT
        $currentRating = $this->RiskAssessment?->name ?? $this->incident->rating_name;
        $kttRequirementMet = in_array($currentRating, ['Sedang', 'Tinggi', 'Ekstrem'])
            ? filled($this->incident->ktt_id)
            : true;

        $allReviewComplete = $reviewInternalMet && $reviewOhsMet && $contractorRequirementMet && $kttRequirementMet;

        // --- Check Realisasi ---
        // Gunakan collection $allActions yang sudah diambil di atas (Hemat memory/CPU)
        $isAllActionClosed = $totalActions > 0 && $hasKeyLearning &&
            $allActions->whereNotNull('actual_completion_date')->count() === $totalActions;

        // --- LOGIKA HIERARKI ---
        if (!$investigationComplete || !$actionPlanComplete) {
            return ($hasTeams || $hasAnalysis) ? 'In Progress' : 'Open';
        }

        if (!$allReviewComplete) {
            return 'Waiting Review';
        }

        if (!$isAllActionClosed) {
            return 'Action Required';
        }

        return 'Closed';
    }
    // Mengambil Status Utama (Misal: "In Progress")
    #[Computed]
    public function mainStatus()
    {
        if (str_contains($this->status, ':')) {
            return trim(explode(':', $this->status)[0]);
        }
        return $this->status;
    }

    // Mengambil Detail/Sub Status (Misal: "Teams")
    #[Computed]
    public function subStatus()
    {
        if (str_contains($this->status, ':')) {
            return trim(explode(':', $this->status)[1]);
        }
        return null;
    }
    public function deleteMedia($id)
    {
        // 1. Cari data attachment berdasarkan ID
        $attachment = IncidentAttachment::find($id);

        if ($attachment) {
            try {
                // 2. Hapus file fisik dari folder storage (storage/app/public/...)
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }

                // 3. Hapus record dari database
                $attachment->delete();

                // 4. Refresh data existing untuk UI
                $this->mount($this->incidentId);

                $this->dispatch('alert', [
                    'text' => "File " . $attachment->file_name . " berhasil dihapus permanen.",
                    'type' => 'success'
                ]);
            } catch (\Exception $e) {
                $this->dispatch('alert', [
                    'text' => "Gagal menghapus file: " . $e->getMessage(),
                    'type' => 'error'
                ]);
            }
        }
    }
    public function deleteFileFromDb($fileId)
    {
        $file = IncidentAttachment::findOrFail($fileId); // Pastikan nama model sesuai tabel Anda

        // 1. Hapus file fisiknya dari storage
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        // 2. Hapus datanya dari database
        $file->delete();

        // 3. Refresh data existing agar UI terupdate
        $report = IncidentReport::with('attachments')->find($this->incidentId);
        $this->existing_supporting_documents = $report->attachments->where('file_type', 'document')->exists();
        $this->existing_visual_evidence = $report->attachments->where('file_type', 'visual')->exists();
        $this->existing_saved_photos = $report->attachments->where('file_type', 'incident_photo')->exists();

        $this->dispatch('alert', ['type' => 'success', 'text' => 'Dokumen berhasil dihapus.']);
    }
    public function removeFile($property, $index)
    {
        if (isset($this->{$property}[$index])) {
            unset($this->{$property}[$index]);
            $this->{$property} = array_values($this->{$property}); // Reset index array
        }
    }
    public function updatedIncidentPhoto()
    {
        try {
            // 1. Validasi setiap file di dalam array secara real-time
            $this->validateOnly('incident_photo.*', [
                'incident_photo.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            ], [
                'incident_photo.*.image' => 'File harus berupa gambar.',
                'incident_photo.*.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
                'incident_photo.*.max'   => 'Ukuran foto maksimal 2MB.',
            ]);

            /**
             * 2. Logika Pembersihan (Cleanup)
             * Jika Anda ingin setiap kali upload baru menghapus foto lama (Single-batch upload),
             * gunakan loop di bawah. Tapi jika sistemnya "tambah foto", bagian ini dilewati.
             */
            if (!empty($this->saved_photos)) {
                foreach ($this->saved_photos as $oldPath) {
                    // Pastikan hanya menghapus file temporary jika diperlukan,
                    // atau hapus file lama jika user mengganti seluruh batch foto.
                    FileHelper::deleteFile($oldPath);
                }
            }

            // 3. Reset array path untuk menampung hasil kompresi yang baru
            $this->saved_photos = [];

            // 4. Looping, Compress, dan Store
            foreach ($this->incident_photo as $file) {
                $this->saved_photos[] = FileHelper::compressAndStore(
                    $file,
                    'incident/incident_photos/evidence'
                );
            }

            // Opsional: Clear temporary upload object setelah diproses ke saved_photos
            // agar tidak membebani memori browser/server
            // $this->incident_photo = [];

        } catch (\Illuminate\Validation\ValidationException $e) {
            // 5. AUTO-CLEAR jika validasi gagal
            $this->incident_photo = [];

            // Tetap biarkan saved_photos yang lama jika ingin foto sebelumnya tidak hilang,
            // atau kosongkan jika ingin reset total.

            throw $e;
        }
    }
    public function updatedVisualEvidence()
    {
        try {
            // 1. Validasi setiap file di dalam array secara real-time
            $this->validateOnly('visual_evidence.*', [
                'visual_evidence.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            ], [
                'visual_evidence.*.image' => 'File harus berupa gambar.',
                'visual_evidence.*.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
                'visual_evidence.*.max'   => 'Ukuran foto maksimal 2MB.',
            ]);

            // 2. Jika validasi lolos, bersihkan file lama dari storage (jika ada)
            if (!empty($this->visual_evidence_paths)) {
                foreach ($this->visual_evidence_paths as $oldPath) {
                    FileHelper::deleteFile($oldPath);
                }
            }

            // 3. Reset array path untuk data baru
            $this->visual_evidence_paths = [];

            // 4. Looping dan simpan (Compress) hanya jika file valid
            foreach ($this->visual_evidence as $file) {
                $this->visual_evidence_paths[] = FileHelper::compressAndStore(
                    $file,
                    'incident/visual_evidence/documentation'
                );
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 5. AUTO-CLEAR: Jika ada file yang salah format (seperti PDF),
            // kita reset array-nya agar preview file yang salah hilang dari UI.
            $this->visual_evidence = [];

            // Lempar kembali error agar muncul di komponen x-form.upload
            throw $e;
        }
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
    public function addCorrectiveRow()
    {
        $this->corrective_actions[] = [
            'action_description' => '',
            'control_hierarchy' => '',
            'pic_user_id' => '',
            'name' => '',

            'due_date' => '',
            'actual_completion_date' => null,
        ];
    }

    public function removeCorrectiveRow($index)
    {
        unset($this->corrective_actions[$index]);
        $this->corrective_actions = array_values($this->corrective_actions);
        $this->searchPetugas = array_values($this->searchPetugas);
    }
    public function updatedSearchPetugas($value, $key)
    {
        // Ambil index dari key (misal "0" dari "searchPetugas.0")
        $index = explode('.', $key)[0];

        /** * RESET DATA LAMA
         * Setiap kali user mengetik (mengubah isi search input),
         * kita anggap pilihan sebelumnya sudah tidak valid lagi.
         */
        $this->corrective_actions[$index]['pic_user_id'] = null;
        $this->corrective_actions[$index]['name']        = null;
        $this->corrective_actions[$index]['id_number']   = null;
        $this->corrective_actions[$index]['dept_con']    = null;

        if (strlen($value) > 1) {
            $this->pelaporsAct = User::where('name', 'like', '%' . $value . '%')
                ->orderBy('name')
                ->limit(20)
                ->get();

            $this->showDropdownPetugas[$index] = true;
        } else {
            $this->showDropdownPetugas[$index] = false;
            // Kosongkan opsi jika pencarian terlalu pendek
            $this->pelaporsAct = [];
        }

        // Jalankan validasi ulang agar error "required_without" muncul kembali
        // karena data pic_user_id & name baru saja di-reset di atas.
        $this->validateOnly("corrective_actions.$index.name");
        $this->validateOnly("corrective_actions.$index.pic_user_id");
    }

    public function selectActPelapor($id, $name)
    {
        // Cari index mana yang dropdown-nya sedang terbuka (true)
        $index = collect($this->showDropdownPetugas)->search(true);

        if ($index !== false) {
            $inspector = User::find($id);

            if ($inspector) {
                // CARA TERBAIK: Update key spesifik tanpa menghapus data lama (action_description, dll)
                $this->corrective_actions[$index]['name'] = $inspector->name;
                $this->corrective_actions[$index]['id_number'] = $inspector->employee_id;
                $this->corrective_actions[$index]['dept_con'] = $inspector->department_name;
                $this->corrective_actions[$index]['pic_user_id'] = $inspector->id;

                // Atau jika ingin menggunakan array_merge:
                // $this->corrective_actions[$index] = array_merge($this->corrective_actions[$index], [
                //     'name' => $inspector->name,
                //     'inspector_id' => $inspector->id,
                //     'id_number' => $inspector->employee_id,
                //     'dept_con' => $inspector->department_name,
                // ]);
            }

            // Update search input agar input field menampilkan nama pilihan
            $this->searchPetugas[$index] = $name;

            // Tutup dropdown
            $this->showDropdownPetugas[$index] = false;
        }
    }

    public function enableManualPetugas($index = null)
    {
        if ($index === null) return;

        $this->manualModePetugas[$index] = true;

        // Pindahkan teks yang sudah diketik di search ke input manual
        $this->manualNamePetugas[$index] = $this->searchPetugas[$index] ?? '';

        // Pastikan dropdown tetap terbuka agar input manualnya terlihat
        $this->showDropdownPetugas[$index] = true;
    }

    /**
     * Menyimpan data nama manual ke dalam array corrective_actions
     */
    public function addManualPetugas($index = null)
    {
        if ($index === null) return;

        $name = $this->manualNamePetugas[$index] ?? '';

        if (!empty($name)) {
            // Simpan ke array utama (pic_user_id dikosongkan karena manual)
            $this->corrective_actions[$index]['name'] = $name;
            $this->corrective_actions[$index]['pic_user_id'] = null;
            $this->corrective_actions[$index]['id_number'] = 'MANUAL';
            $this->corrective_actions[$index]['dept_con'] = '-';

            // Update search field agar sinkron
            $this->searchPetugas[$index] = $name;
        }

        // Reset UI State
        $this->manualModePetugas[$index] = false;
        $this->showDropdownPetugas[$index] = false;
        $this->validateOnly("corrective_actions.$index.name");
        $this->validateOnly("corrective_actions.$index.pic_user_id");
        // Simpan ke session atau trigger validasi jika perlu
        $this->saveToSession();
    }



    /**
     * Helper Function untuk Reset Dropdown
     */
    private function resetDropdowns()
    {
        $this->showPenerimaanKomentarContractorDropdown = false;
        $this->showPenerimaanKomentarInternalDropdown = false;
        $this->showPenerimaanKomentarOhsDropdown = false;
        $this->showPenerimaanKomentarKttDropdown = false;
    }

    /**
     * Action: Pilih Pelapor Contractor
     */
    public function selectPenerimaanKomentarContractor($id, $name)
    {
        $this->penerimaan_komentar_contractor_id = $id;
        $this->searchNamePenerimaan['kontraktor'] = $name;
        $this->resetDropdowns();
    }

    /**
     * Action: Pilih Pelapor Internal
     */
    public function selectPenerimaanKomentarInternal($id, $name)
    {
        $this->penerimaan_komentar_internal_id = $id;
        $this->searchNamePenerimaan['internal'] = $name;
        $this->resetDropdowns();
    }

    /**
     * Action: Pilih Pelapor OHS
     */
    public function selectPenerimaanKomentarOhs($id, $name)
    {
        $this->penerimaan_komentar_ohs_id = $id;
        $this->searchNamePenerimaan['ohs'] = $name;
        $this->resetDropdowns();
    }

    /**
     * Action: Pilih Pelapor KTT
     */
    public function selectPenerimaanKomentarKtt($id, $name)
    {
        $this->penerimaan_komentar_ktt_id = $id;
        $this->searchNamePenerimaan['ktt'] = $name;
        $this->resetDropdowns();
    }

    /**
     * Lifecycle: Monitor perubahan search input untuk memunculkan dropdown
     */
    public function updatedSearchNamePenerimaan($value, $key)
    {
        // Mapping key input ke activeType agar logic pencarian sinkron
        $map = [
            'kontraktor' => 'penerimaan_komentar_contractor',
            'internal'   => 'penerimaan_komentar_internal',
            'ohs'        => 'penerimaan_komentar_ohs',
            'ktt'        => 'penerimaan_komentar_ktt',
        ];

        if (isset($map[$key])) {
            $this->activeTypePenerimaan = $map[$key];
        }

        $this->resetDropdowns();

        // Tampilkan dropdown yang sesuai
        if ($key === 'kontraktor') $this->showPenerimaanKomentarContractorDropdown = true;
        if ($key === 'internal') $this->showPenerimaanKomentarInternalDropdown = true;
        if ($key === 'ohs') $this->showPenerimaanKomentarOhsDropdown = true;
        if ($key === 'ktt') $this->showPenerimaanKomentarKttDropdown = true;
    }

    public function getPelaporsPenerimaanProperty()
    {
        // Ambil keyword berdasarkan activeTypePenerimaan
        $keyMapping = [
            'penerimaan_komentar_contractor' => 'kontraktor',
            'penerimaan_komentar_internal'   => 'internal',
            'penerimaan_komentar_ohs'        => 'ohs',
            'penerimaan_komentar_ktt'        => 'ktt',
        ];

        $type = $keyMapping[$this->activeTypePenerimaan] ?? null;
        $searchTerm = $type ? ($this->searchNamePenerimaan[$type] ?? '') : '';

        if (strlen($searchTerm) < 2) {
            return [];
        }

        return User::where('name', 'like', '%' . $searchTerm . '%')
            ->limit(20) // 80 terlalu banyak untuk dropdown, 20 saja agar cepat
            ->get();
    }

    /**
     * Mendefinisikan pesan error kustom
     */

    /**
     * Helper untuk menembakkan event validasi ke frontend
     */
    protected function dispatchValidationEvents($errors)
    {
        $komentarFields = [
            'penerimaan_komentar_contractor',
            'penerimaan_komentar_internal',
            'penerimaan_komentar_ohs',
            'penerimaan_komentar_ktt'
        ];

        foreach ($komentarFields as $field) {
            if ($errors->has($field)) {
                $this->dispatch('validate-' . $field);
            }
        }
    }

    public function getUnsafeConditionOptionsProperty()
    {
        return [
            '1.1.1 Tidak memadainya pengamanan atau penghalang' => '1.1.1 Tidak memadainya pengamanan atau penghalang',
            '1.1.2 Tidak memadainya atau tidak layaknya peralatan pencegah' => '1.1.2 Tidak memadainya atau tidak layaknya peralatan pencegah',
            '1.1.3 Perkakas, peralatan atau bahan(material) yang rusak' => '1.1.3 Perkakas, peralatan atau bahan(material) yang rusak',
            '1.1.4 Tempat kerja sangat terbatas' => '1.1.4 Tempat kerja sangat terbatas',
            '1.1.5 Kurang memadainya Sistem peringatan' => '1.1.5 Kurang memadainya Sistem peringatan',
            '1.1.6 Bahaya kebakaran dan ledakan' => '1.1.6 Bahaya kebakaran dan ledakan',
            '1.1.7 Housekeeping jelek/berantakan' => '1.1.7 Housekeeping jelek/berantakan',
            '1.1.8 Kebisingan' => '1.1.8 Kebisingan',
            '1.1.9 Radiasi' => '1.1.9 Radiasi',
            '1.1.10 Suhu yang Ekstrem' => '1.1.10 Suhu yang Ekstrem',
            '1.1.11 Kurangnya penerangan / berlebihan' => '1.1.11 Kurangnya penerangan / berlebihan',
            '1.1.12 Ventilasi' => '1.1.12 Ventilasi',
            '1.1.13 Kondisi lingkungan yang berbahaya' => '1.1.13 Kondisi lingkungan yang berbahaya',
            '1.1.14 Lainnya' => '1.1.14 Lainnya',
        ];
    }
    #[Computed]
    public function unsafeActOptions()
    {
        return ScatOption::where('type', 'unsafe_act')
            ->get()
            ->pluck('full_label', 'full_label');
    }
    #[Computed]
    public function personalFactorOptions()
    {
        return ScatOption::where('type', 'personal_factor')
            ->get()
            ->pluck('full_label', 'full_label');
    }

    #[Computed]
    public function jobFactorOptions()
    {
        return ScatOption::where('type', 'job_factor')
            ->get()
            ->pluck('full_label', 'full_label');
    }

    #[Computed]
    public function controlSystemOptions()
    {
        return ScatOption::where('type', 'control_system')
            ->get()
            ->pluck('full_label', 'full_label');
    }

    #[Computed]
    public function gridClass()
    {
        return match (true) {
            $this->whyCount == 2 => 'grid-cols-2',
            $this->whyCount >= 3 => 'grid-cols-3',
            default => 'grid-cols-1',
        };
    }
    public function addWhyColumn()
    {
        $this->whyCount++;

        // Inisialisasi key baru di setiap baris timeline agar tidak error
        $this->why_analysis['why' . $this->whyCount] = '';
        $this->saveToSession();
    }

    public function removeWhyColumn()
    {
        if ($this->whyCount > 1) {
            unset($this->why_analysis['why' . $this->whyCount]);
            $this->whyCount--;
        }
        $this->saveToSession();
    }

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
        $this->directly_involved[$index]['employee_name'] = null;
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
            session(['incident_data' => $this->all()]);

            // 5. Opsional: Trigger validasi untuk baris tersebut
            $this->validateOnly("directly_involved.$index.*");
        }
    }
    // 1. Fungsi untuk mengaktifkan inputan manual
    public function enableManualMode($index)
    {
        $this->manualMode[$index] = true;

        // Opsional: Isi field manual dengan apa yang sudah diketik di search box
        $this->manualEmployeeName[$index] = $this->searchKorban[$index] ?? '';
    }

    // 2. Fungsi untuk menyimpan data manual ke dalam baris directly_involved
    public function addManualData($index)
    {
        // Validasi sederhana jika perlu
        if (empty($this->manualEmployeeName[$index])) {
            return;
        }

        // Masukkan ke array utama
        $this->directly_involved[$index]['employee_name'] = $this->manualEmployeeName[$index];
        $this->directly_involved[$index]['employee_id']   = null; // Beri null karena tidak ada di DB
        $this->directly_involved[$index]['employee_nik']  = 'isi manual';

        // Reset state search dan dropdown
        $this->searchKorban[$index] = $this->manualEmployeeName[$index];
        $this->show_employee_dropdown[$index] = false;
        $this->manualMode[$index] = false;

        // Simpan ke session seperti fungsi selectInvolvedPersonnel Anda
        $this->saveToSession();
    }




    public function addRow($type)
    {
        // 1. Tentukan struktur data berdasarkan tipe
        // Tambahkan pengecekan untuk faktor pribadi, pekerjaan, dan sistem kontrol
        if (in_array($type, ['unsafe_conditions', 'unsafe_acts', 'personal_factors', 'job_factors', 'control_system_factors'])) {
            $newData = ['item' => '', 'description' => ''];
        } elseif ($type === 'timelines') {
            // Timeline dengan struktur khusus sesuai jumlah kolom "Why"
            $newData = ['kegiatan' => '', 'tanggal' => ''];
            for ($i = 1; $i <= $this->whyCount; $i++) {
                $newData["why{$i}"] = '';
            }
        } else {
            // Default untuk partisipan (pemimpin, facilitator, anggota)
            $newData = ['user_id' => null, 'nama' => '', 'jabatan' => '', 'jabatan_detail' => '', 'dept' => ''];
        }

        // 2. Masukkan ke array utama secara dinamis
        $this->{$type}[] = $newData;

        // 3. Inisialisasi state pembantu untuk pencarian User
        if (in_array($type, ['pemimpin', 'facilitator', 'anggota',])) {
            $newIndex = count($this->{$type}) - 1;
            $this->searchQuery[$newIndex][$type] = '';
            $this->showDropdownPartisipan[$newIndex] = false;
        }
        $this->saveToSession();
    }
    public function removeRow($type, $index)
    {
        // 1. Hapus data baris utama
        if (isset($this->{$type}[$index])) {
            unset($this->{$type}[$index]);
            $this->{$type} = array_values($this->{$type});
        }

        // 2. Sinkronisasi searchQuery dan Dropdown
        // Kita tidak bisa hanya array_values secara global karena akan menggeser
        // data tipe lain yang berada di indeks yang sama.

        // Cara terbaik: Hapus data indeks tersebut, lalu geser manual data di bawahnya
        // khusus untuk tipe (key) yang sedang dihapus.

        $totalRemaining = count($this->{$type});

        for ($i = $index; $i <= $totalRemaining; $i++) {
            // Geser searchQuery untuk tipe yang spesifik ini saja
            if (isset($this->searchQuery[$i + 1][$type])) {
                $this->searchQuery[$i][$type] = $this->searchQuery[$i + 1][$type];
                unset($this->searchQuery[$i + 1][$type]);
            } else {
                unset($this->searchQuery[$i][$type]);
            }

            // Geser status dropdown
            if (isset($this->showDropdownPartisipan[$i + 1])) {
                $this->showDropdownPartisipan[$i] = $this->showDropdownPartisipan[$i + 1];
            } else {
                unset($this->showDropdownPartisipan[$i]);
            }
        }

        // 3. Jika baris habis, tambahkan satu baris kosong lagi
        if (empty($this->{$type})) {
            $this->addRow($type);
        }
        $this->saveToSession();
    }
    public function updatedSearchQuery($value, $key)
    {
        // $key sekarang berisi "0.pemimpin", "1.facilitator", dst.
        $parts = explode('.', $key);
        $index = $parts[0]; // Mendapatkan angka index

        if (strlen($value) < 2) {
            $this->options = [];
            $this->showDropdownPartisipan[$index] = false;
            return;
        }

        $this->options = User::where('name', 'like', '%' . $value . '%')->limit(50)->get();

        // Buka dropdown berdasarkan index barisnya
        $this->showDropdownPartisipan[$index] = true;
    }
    public function selectUser($id, $index, $type)
    {
        $user = User::find($id);

        if ($user) {
            // 1. Set data utama
            $this->{$type}[$index]['user_id'] = $user->id;
            $this->{$type}[$index]['name']    = $user->name;

            // 2. Set default Jabatan & Dept (Sangat membantu user agar tidak ketik manual)
            $this->{$type}[$index]['jabatan'] = $user->position ?? '';
            $this->{$type}[$index]['dept']    = $user->department_name ?? '';

            // 3. Update teks input pencarian agar sinkron dengan pilihan
            // Pastikan $this->searchQuery sudah didefinisikan sebagai array di awal
            $this->searchQuery[$index][$type] = $user->name;

            // 4. Reset state dropdown
            $this->showDropdownPartisipan[$index] = false;
            $this->options = [];

            // 5. TRIGGER VALIDASI (PENTING)
            // Menghapus pesan error merah segera setelah data terpilih
            $this->validateOnly($type . '.' . $index . '.user_id');
            $this->validateOnly($type . '.' . $index . '.dept');
            $this->validateOnly($type . '.' . $index . '.jabatan');
            // SIMPAN KE SESSION
            $this->saveToSession();
        }
    }
    public function enableManualPartisipan($index, $type)
    {
        // Aktifkan mode manual untuk index dan tipe spesifik
        $this->manualMode[$index][$type] = true;

        // Copy apa yang sudah diketik di search ke input manual
        $this->manualName[$index][$type] = $this->searchQuery[$index][$type] ?? '';

        // Pastikan dropdown tetap terbuka untuk menampilkan input manual
        $this->showDropdownPartisipan[$index] = true;
    }

    public function addManualPartisipan($index, $type)
    {
        $name = $this->manualName[$index][$type] ?? '';

        if (empty($name)) return;

        // 1. Set data ke array utama (sesuai struktur selectUser Anda)
        $this->{$type}[$index]['user_id'] = null; // null karena manual (tidak ada di DB)
        $this->{$type}[$index]['name']    = $name;

        // 2. Set Jabatan/Dept default jika kosong
        $this->{$type}[$index]['jabatan'] = $this->{$type}[$index]['jabatan'] ?? 'Manual Input';
        $this->{$type}[$index]['dept']    = $this->{$type}[$index]['dept'] ?? '';

        // 3. Sinkronkan tampilan search input
        $this->searchQuery[$index][$type] = $name;

        // 4. Reset states
        $this->manualMode[$index][$type] = false;
        $this->showDropdownPartisipan[$index] = false;
        $this->options = [];

        // 5. Simpan & Validasi
        $this->validateOnly($type . '.' . $index . '.name');
        $this->saveToSession();
    }
    public function resetSearch()
    {
        $this->searchQuery = []; // Reset ke array kosong
        $this->options = [];
        $this->showDropdownPartisipan = []; // Reset ke array kosong
        $this->manualMode = []; // Reset ke array kosong
        $this->manualName = []; // Reset ke array kosong
        $this->activeType = '';
        $this->activeIndex = null;
    }

    public function updatedEventTypeId($value)
    {
        // 1. Reset sub-type setiap kali tipe utama berubah
        $this->event_sub_type_id = null;

        // 2. Tentukan logic isInjury berdasarkan Tipe Insiden (Event Type)
        // Asumsi: ID atau Nama tertentu menentukan apakah ini cidera manusia
        // Anda bisa menyesuaikan ID di bawah sesuai database SENTRY Anda
        $eventType = EventType::find($value);

        if ($eventType) {
            // Contoh logic: Jika nama tipe mengandung kata 'Cidera' atau 'Injury'
            if (
                str_contains(strtolower($eventType->event_type_name), 'injury') ||
                str_contains(strtolower($eventType->event_type_name), 'cidera')
            ) {
                $this->isInjury = true;
                $this->damage_detail = null; // Reset detail kerusakan alat
            } else {
                $this->isInjury = false;
                $this->selectedBodyParts = null; // Reset data cidera
                $this->selectedBodyPartCategory = null;
            }
        }
    }
    // Helper untuk memetakan field mana masuk ke step mana



    public function getHasSubTypesProperty()
    {
        if (!$this->event_type_id) {
            return false;
        }

        // Cek apakah ada anak (sub-tipe) untuk tipe yang dipilih
        return EventSubType::where('event_type_id', $this->event_type_id)->exists();
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

        if ($this->contractor_id) {
            $this->penerimaan_komentar_contractor_id = null;
            $this->searchNamePenerimaan['kontraktor'] = '';
            if (isset($this->searchNamePenerimaan['kontraktor'])) {
                $this->searchNamePenerimaan['kontraktor'] = '';
            }
            $this->dispatch('reset-all-editors');
        } else {
            $this->dispatch('refresh-contractor-editor');
        }

        if (!in_array($this->rating_name, ['Sedang', 'Tinggi', 'Ekstrem'])) {
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
        // 1. Guard clause jika input ID belum lengkap
        if (!$this->likelihood_id || !$this->consequence_id) {
            $this->RiskAssessment = null;
            $this->rating_name = null; // Pastikan rating di-reset juga
            return;
        }

        // 2. Cari cell berdasarkan persilangan likelihood dan consequence
        $cell = RiskMatrixCell::where('likelihood_id', $this->likelihood_id)
            ->where('risk_consequence_id', $this->consequence_id)
            ->first();

        if (!$cell) {
            $this->RiskAssessment = null;
            $this->rating_name = null;
            return;
        }

        // 3. Ambil data matriks risiko
        $matrix = RiskAssessmentMatrix::where('risk_matrix_cell_id', $cell->id)->first();

        // 4. Load model RiskAssessment (Sedang, Tinggi, Ekstrem, dll)
        $this->RiskAssessment = $matrix ? RiskAssessment::find($matrix->risk_assessment_id) : null;

        // 5. PENYEBAB ERROR: Gunakan null-safe operator agar tidak crash saat RiskAssessment null
        $this->rating_name = $this->RiskAssessment?->name;
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
    /**
     * Logika Validasi Tahap Akhir (Otorisasi & Komentar)
     * @return bool
     */
    public function checkStep9Status(): bool
    {
        // 1. Ambil data pendukung (asumsi data ini ada di model Incident atau properti component)
        $requiresKTT = in_array($this->incident->rating_name, ['Sedang', 'Tinggi', 'Ekstrem']);
        $hasContractor = !empty($this->incident->contractor_id);

        // 2. Cek Komentar OHS (Wajib untuk semua level)
        $ohsDone = !empty($this->incident->ohs_head_id);
        $internalDone = !empty($this->incident->pm_internal_id);

        // 3. Cek Komentar KTT (Hanya jika rating Sedang ke atas)
        // Jika tidak butuh KTT, otomatis dianggap true
        $kttDone = $requiresKTT ? !empty($this->incident->ktt_id) : true;

        // 4. Cek Komentar Kontraktor (Hanya jika ada contractor_id)
        // Jika tidak ada kontraktor, otomatis dianggap true
        $contractorDone = $hasContractor ? !empty($this->incident->pm_contractor_id) : true;

        // Output: Hanya True jika SEMUA syarat yang relevan terpenuhi
        return $ohsDone && $internalDone && $kttDone && $contractorDone;
    }
    // Di dalam Update.php

    /**
     * Mendapatkan status akses edit per step
     */
    public function getCanEditProperty($step)
    {
        if ($this->incident->status === 'Closed') {
            return false;
        }

        return match ($step) {
            1, 2 => Auth::user()->can('updateInitialData', $this->incident),
            3 => Auth::user()->can('updateTeamInvestigation', $this->incident),
            4, 5, 6 => Auth::user()->can('conductInvestigation', $this->incident),
            7 => Auth::user()->can('manageCorrectiveActions', $this->incident),
            8 => Auth::user()->can('updateLessonsLearned', $this->incident),
            9 => Auth::user()->can('reviewReport', $this->incident),
            default => false
        };
    }

    /**
     * Mendapatkan Nama Step (Bisa ditaruh di konstanta atau function)
     */
    public function getStepTitle($step)
    {
        return [
            1 => 'Detil Laporan',
            2 => 'Pihak Terlibat Langsung',
            3 => 'Partisipan Investigasi',
            4 => 'PEEPO Investigation Factor',
            5 => 'Time Line & Analisis',
            6 => 'Investigasi Kecelakaan (Checklist)',
            7 => 'Tindakan Perbaikan & Dokumentasi',
            8 => 'Kunci Pembelajaran',
            9 => 'Penerimaan & Komentar Reviewer',
        ][$step] ?? '';
    }

    /**
     * Mengecek apakah step tertentu memiliki error validasi
     */
    public function hasErrorInStep($step)
    {
        $fields = $this->getFieldsForStep($step);

        foreach ($fields as $field) {
            // Mengakses error bag dari Livewire
            if ($this->getErrorBag()->has($field) || $this->getErrorBag()->has($field . '.*')) {
                return true;
            }
        }

        return false;
    }
    public function reopen()
    {
        // 1. Validasi Otoritas (Hanya yang punya permission 'reviewReport' atau Admin)
        if (!Gate::allows('reviewReport', $this->incident)) {
            $this->addError('reopen', 'Anda tidak memiliki otoritas untuk membuka kembali laporan ini.');
            return;
        }

        // 2. Update Status
        $this->incident->update([
            'status' => 'In Progress'
        ]);

        // 3. Refresh status property di Livewire agar UI terupdate
        $this->status = 'In Progress';

        // 4. (Optional) Tambahkan Log Activity
        // activity()->performedOn($this->incident)->log('Laporan dibuka kembali untuk perbaikan data.');

        $this->dispatch('toast', message: 'Laporan berhasil dibuka kembali.', variant: 'success');
    }
    public function render()
    {

        $stepStatus = [
            'step1' => !empty($this->incident->event_type_id),

            // Ganti _count dengan memanggil relasi secara langsung agar fresh (reaktif)
            'step2' => $this->incident->involvedPersons()->exists(),
            'step3' => $this->incident->investigationTeams()->exists(),
            'step4' => $this->incident->peepoAnalyses()->whereNotNull('temuan')->where('temuan', '!=', '')->exists(),
            'step5' => $this->incident->timelines()->where('why_count_used', '!=', 0)->exists(),
            'step6' => collect($this->incident->scat_analysis)->flatten()->contains(fn($value) => !empty($value) && $value !== ""),
            'step7' => $this->incident->correctiveActions()->exists() &&
                $this->incident->correctiveActions()->whereNull('actual_completion_date')->count() === 0 && $this->incident->attachments()->exists(),
            // Pastikan variabel ini sinkron dengan properti component
            'step8' => !empty($this->key_learning) || !empty($this->incident->key_learning),

            'step9' => $this->checkStep9Status(),
        ];

        $users = User::with('roles')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Kepala Teknik Tambang (KTT)');
            })
            ->get();
        return view('livewire.incident.update', [
            'Department'   => Department::all(),
            'Ktt' => $users,
            'Contractors'  => Contractor::all(),
            'likelihoodss' => Likelihood::orderByDesc('level')->get(),
            'consequencess' => RiskConsequence::orderBy('level')->get(),
            'eventTypes' => EventType::onlyIncidents()->get(),
            'eventSubTypes' => EventSubType::where('event_type_id', $this->event_type_id)->get(),
            'ktas' => UnsafeCondition::latest()->get(),
            'ttas' => UnsafeAct::latest()->get(),
            'allStepsData' => $stepStatus
        ]);
    }

    public function validateCurrentStep()
    {
        $fields = [];

        switch ($this->currentStep) {
            case 1:
                $fields = [
                    'title',
                    'event_type_id',
                    'event_sub_type_id', // Muncul jika hasSubTypes
                    'potential_lti',
                    'env_classification', // Muncul jika isEnvironmentType
                    'date_time',
                    'location_id',
                    'location_specific',
                    'contract_area_name',
                    'department_id',
                    'contractor_id',
                    'deptCont', // Untuk validasi pilihan Dept vs Cont
                    'penanggungJawab', // Pastikan case-sensitive sesuai wire:model
                    'pelapor_id',
                    'manualPelaporName', // Untuk mode manual pelapor
                    'consequence_id',
                    'likelihood_id',
                    'tasks',
                    'description',
                    'emergency_action',
                    'selectedBodyPartCategory', // Muncul jika isInjury
                    'selectedBodyParts',         // Muncul jika isInjury
                    'damage_detail',            // Muncul jika !isInjury
                ];
                break;

            case 2:
                // Tambahkan field untuk Part 2 (Saksi, korban, dll)
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
            case 3:
                $fields = [
                    'pemimpin',
                    'pemimpin.*.user_id',
                    'pemimpin.*.name',
                    'pemimpin.*.dept',
                    'pemimpin.*.jabatan',
                    'facilitator',
                    'facilitator.*.user_id',
                    'facilitator.*.name',
                    'facilitator.*.dept',
                    'facilitator.*.jabatan',
                    'anggota',
                    'anggota.*.user_id',
                    'anggota.*.name',
                    'anggota.*.dept',
                    'anggota.*.jabatan',
                ];
                break;
            case 4:
                $fields = [];

                foreach (array_keys($this->peepoFactors) as $key) {

                    $fields[] = "peepo.$key";

                    $fields[] = "peepo.$key.*.temuan";

                    $fields[] = "peepo.$key.*.deskripsi";
                }

                break;
            case 5:
                // Hapus 'timelines.*.kejadian' karena kita tidak pakai baris timeline lagi
                $fields = [];

                // Daftarkan semua kolom why yang aktif di dalam properti why_analysis
                for ($i = 1; $i <= $this->whyCount; $i++) {
                    $fields[] = "why_analysis.why{$i}";
                }
                break;
            case 6:
                $fields = [
                    'unsafe_conditions.*.item',
                    'unsafe_conditions.*.description',
                    'unsafe_acts.*.item',
                    'unsafe_acts.*.description',
                    'personal_factors.*.item',
                    'personal_factors.*.description',
                    'job_factors.*.item',
                    'job_factors.*.description',
                    'control_system_factors.*.item',
                    'control_system_factors.*.description',
                ];
                break;
            case 7:
                $fields = [
                    'visual_evidence',
                    'visual_evidence.*',
                    'supporting_documents',
                    'supporting_documents.*',

                    // Tabel Tindakan Perbaikan
                    'corrective_actions.*.action_description',
                    'corrective_actions.*.control_hierarchy',
                    // REVISI: Samakan dengan properti yang menyimpan ID User (bukan Nama)
                    'corrective_actions.*.pic_user_id',
                    'corrective_actions.*.name', // Ini untuk validasi nama PIC, tapi pastikan di rules() Anda validasi pic_user_id juga
                    'corrective_actions.*.due_date',
                    // Tambahkan ini jika Anda mewajibkan tanggal selesai diisi di Step 7
                    // 'corrective_actions.*.actual_completion_date',
                ];
                break;
            case 8:
                $fields = [
                    'key_learning'
                ];
                $this->dispatch('validate-key_learning');
                break;
            case 9:
                $fields = [

                    'penerimaan_komentar_internal_id',
                    'penerimaan_komentar_ohs_id',
                    'penerimaan_komentar_internal',
                    'penerimaan_komentar_ohs'
                ];
                if ($this->contractor_id) {
                    $fields = array_merge($fields, ['penerimaan_komentar_contractor_id', 'penerimaan_komentar_contractor']);
                }
                // Tambahkan field KTT ke dalam daftar fields jika level 3, 4, atau 5
                if (in_array($this->rating_name, ['Sedang', 'Tinggi', 'Ekstrem'])) {
                    // Masukkan ke daftar fields agar dikenali sistem
                    $fields = array_merge($fields, ['penerimaan_komentar_ktt_id', 'penerimaan_komentar_ktt']);
                }
        }

        if (!empty($fields)) {
            // Pastikan rules() adalah PUBLIC
            $allRules = $this->rules();

            // Filter rules hanya untuk field yang ada di step aktif
            $stepRules = array_intersect_key($allRules, array_flip($fields));

            // Validasi dengan atribut dan pesan custom
            $this->validate($stepRules, $this->messages(), $this->validationAttributes());
        }
    }
    public function nextStep()
    {
        // 1. Validasi input hanya jika user memang punya hak edit di step saat ini
        // Jika user hanya 'Read-Only', lewati validasi dan langsung pindah
        $canEditCurrentStep = $this->checkPolicyForStep($this->currentStep);

        if ($canEditCurrentStep) {
            $this->validateCurrentStep(); // Fungsi validasi custom Anda
        }

        // 2. Tentukan target step selanjutnya
        $next = $this->currentStep + 1;

        // 3. Pastikan user boleh masuk ke step selanjutnya tersebut
        if ($next <= 9) {
            $this->goToStep($next);
        }
    }

    /**
     * Helper untuk menyederhanakan pengecekan policy per step
     */
    private function checkPolicyForStep($step)
    {
        return match (intval($step)) {
            1, 2      => Gate::allows('updateInitialData', $this->incident),
            3      => Gate::allows('updateTeamInvestigation', $this->incident), // Karena step 3 adalah Partisipan Investigasi, kita anggap ini masih bagian dari investigasi
            4, 5, 6 => Gate::allows('conductInvestigation', $this->incident),
            7         => Gate::allows('manageCorrectiveActions', $this->incident),
            8         => Gate::allows('updateLessonsLearned', $this->incident),
            9         => Gate::allows('reviewReport', $this->incident),
            default   => false
        };
    }
    /**
     * Berpindah antar step/part secara langsung
     */
    public function goToStep($step)
    {
        // Gunakan match yang sama dengan di Blade untuk konsistensi
        $canAccess = match (intval($step)) {
            1, 2      => Gate::allows('updateInitialData', $this->incident),
            3      => Gate::allows('updateTeamInvestigation', $this->incident), // Karena step 3 adalah Partisipan Investigasi, kita anggap ini masih bagian dari investigasi
            4, 5, 6 => Gate::allows('conductInvestigation', $this->incident),
            7         => Gate::allows('manageCorrectiveActions', $this->incident),
            8         => Gate::allows('updateLessonsLearned', $this->incident),
            9         => Gate::allows('reviewReport', $this->incident),
            default   => false
        };

        if (!$canAccess) {
            $this->dispatch('alert', [
                'type' => 'error',
                'text' => 'Anda tidak memiliki otoritas untuk mengakses Bagian ' . $step
            ]);
            return;
        }

        $this->currentStep = $step;
        $this->dispatch('scroll-to-top');
    }

    // Di dalam Class Livewire Anda
    public function getIsVisualRequiredProperty()
    {
        // Cek apakah ada Dokumen (di DB atau sedang di-upload)
        $hasDocument = (count($this->existing_supporting_documents) > 0) || (is_array($this->supporting_documents) && count($this->supporting_documents) > 0);

        // Cek juga apakah sudah ada foto di DB
        $hasVisualInDb = count($this->existing_visual_evidence) > 0;

        // Required jika tidak ada dokumen DAN belum ada foto di DB
        return !$hasDocument && !$hasVisualInDb;
    }

    public function getIsDocumentRequiredProperty()
    {
        $hasVisual = (count($this->existing_visual_evidence) > 0) || (is_array($this->visual_evidence) && count($this->visual_evidence) > 0);
        $hasDocInDb = count($this->existing_supporting_documents) > 0;

        return !$hasVisual && !$hasDocInDb;
    }

    protected function validateOnlyStep($step)
    {
        // 1. Ambil semua rules utama dari method rules() sebagai referensi
        $allRules = $this->rules();
        $hasIncidentPhotos = count($this->saved_photos) > 0;
        // 2. Definisikan Base Rules kondisional (untuk digunakan di array_merge)
        $isInjuryRules = $this->isInjury
            ? [
                'selectedBodyPartCategory' => $allRules['selectedBodyPartCategory'],
                'selectedBodyParts' => $allRules['selectedBodyParts'],
                'selectedBodyParts.*'       => $allRules['selectedBodyParts.*'] ?? 'exists:body_parts,id',
            ]
            : [
                'damage_detail' => $allRules['damage_detail']
            ];
        $contractorRules = $this->contractor_id
            ? [
                'penerimaan_komentar_contractor_id' => $allRules['penerimaan_komentar_contractor_id'],
                'penerimaan_komentar_contractor' => $allRules['penerimaan_komentar_contractor']
            ]
            : [];

        $kttRules = in_array($this->rating_name, ['Sedang', 'Tinggi', 'Ekstrem'])
            ? [
                'penerimaan_komentar_ktt_id' => $allRules['penerimaan_komentar_ktt_id'],
                'penerimaan_komentar_ktt' => $allRules['penerimaan_komentar_ktt']
            ]
            : [];
        // 1. Cek ketersediaan Visual (di DB atau di Input)
        $hasVisual = (is_array($this->existing_visual_evidence) && count($this->existing_visual_evidence) > 0)
            || (is_array($this->visual_evidence) && count($this->visual_evidence) > 0);



        // 2. Cek ketersediaan Dokumen (di DB atau di Input)
        $hasDocument = (is_array($this->existing_supporting_documents) && count($this->existing_supporting_documents) > 0)
            || (is_array($this->supporting_documents) && count($this->supporting_documents) > 0);

        $step4Rules = [];

        foreach (array_keys($this->peepoFactors) as $key) {

            $step4Rules["peepo.$key.*.temuan"] =
                $allRules["peepo.$key.*.temuan"];

            $step4Rules["peepo.$key.*.deskripsi"] =
                $allRules["peepo.$key.*.deskripsi"];
        }

        // 3. Pemetaan Rules per Step
        $stepRules = [
            1 => array_merge([
                'title'             => $allRules['title'],
                'event_type_id'      => $allRules['event_type_id'],
                'event_sub_type_id'  => $allRules['event_sub_type_id'],
                'description'        => $allRules['description'],
                'tasks'              => $allRules['tasks'],
                'potential_lti'      => $allRules['potential_lti'],
                'location_id'        => $allRules['location_id'],
                'location_specific'  => $allRules['location_specific'],
                'contract_area_name' => $allRules['contract_area_name'],
                'env_classification' => $allRules['env_classification'],
                'date_time'          => $allRules['date_time'],
                'pelapor_id'         => $allRules['pelapor_id'],
                'department_id'      => $allRules['department_id'],
                'contractor_id'      => $allRules['contractor_id'],
                'deptCont'           => $allRules['deptCont'],
                'likelihood_id'      => $allRules['likelihood_id'],
                'consequence_id'     => $allRules['consequence_id'],
                'emergency_action'   => $allRules['emergency_action'],
                'penanggungJawab'    => $allRules['penanggungJawab'],
                'saved_photos'       => $hasIncidentPhotos ? 'nullable|array' : 'required|array|min:1',
                // Aturan untuk file upload (nullable karena begitu masuk langsung dipindah ke saved_photos)
                'incident_photo.*'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ], $isInjuryRules),

            2 => [
                'directly_involved' => $allRules['directly_involved'],
                'directly_involved.*.employee_name' => $allRules['directly_involved.*.employee_name'],
                'directly_involved.*.employee_nik'  => $allRules['directly_involved.*.employee_nik'],
                'directly_involved.*.dept_cont'     => $allRules['directly_involved.*.dept_cont'],
                'directly_involved.*.jabatan'       => $allRules['directly_involved.*.jabatan'],
                'directly_involved.*.roster'        => $allRules['directly_involved.*.roster'],
                'directly_involved.*.sift'          => $allRules['directly_involved.*.sift'],
                'directly_involved.*.keterlibatan'  => $allRules['directly_involved.*.keterlibatan'],
                'directly_involved.*.pengalaman_kerja' => $allRules['directly_involved.*.pengalaman_kerja'],
            ],

            3 => [
                // Pemimpin Investigasi
                'pemimpin'           => $allRules['pemimpin'],
                'pemimpin.*.user_id' => $allRules['pemimpin.*.user_id'],
                'pemimpin.*.name'    => $allRules['pemimpin.*.name'],
                'pemimpin.*.dept'    => $allRules['pemimpin.*.dept'],
                'pemimpin.*.jabatan' => $allRules['pemimpin.*.jabatan'],

                // Facilitator (KPLH)
                'facilitator'           => $allRules['facilitator'],
                'facilitator.*.user_id' => $allRules['facilitator.*.user_id'],
                'facilitator.*.name'    => $allRules['facilitator.*.name'],
                'facilitator.*.dept'    => $allRules['facilitator.*.dept'],
                'facilitator.*.jabatan' => $allRules['facilitator.*.jabatan'],

                // Tim Anggota
                'anggota'           => $allRules['anggota'],
                'anggota.*.user_id' => $allRules['anggota.*.user_id'],
                'anggota.*.name'    => $allRules['anggota.*.name'],
                'anggota.*.dept'    => $allRules['anggota.*.dept'],
                'anggota.*.jabatan' => $allRules['anggota.*.jabatan'],
            ],

            4 => $step4Rules,

            5 => $this->getWhyAnalysisRules(),

            6 => [
                'unsafe_conditions.*.item' => $allRules['unsafe_conditions.*.item'],
                'unsafe_conditions.*.description' => $allRules['unsafe_conditions.*.description'],
                'unsafe_acts.*.item' => $allRules['unsafe_acts.*.item'],
                'personal_factors.*.item' => $allRules['personal_factors.*.item'],
                'job_factors.*.item' => $allRules['job_factors.*.item'],
                'control_system_factors.*.item' => $allRules['control_system_factors.*.item'],
            ],

            7 => [
                // Jika sudah ada file di database, visual_evidence baru harusnya nullable

                'visual_evidence' => (!$hasDocument)
                    ? 'required|array|min:1'
                    : 'nullable|array',

                'visual_evidence.*' => 'image|mimes:jpg,jpeg,png|max:2048',

                // Supporting Documents: Required hanya jika TIDAK ADA visual sama sekali
                'supporting_documents' => (!$hasVisual)
                    ? 'required|array|min:1'
                    : 'nullable|array',

                'supporting_documents.*' => 'mimes:pdf,doc,docx|max:5120',

                'corrective_actions.*.action_description' => $allRules['corrective_actions.*.action_description'],
                'corrective_actions.*.pic_user_id'         => $allRules['corrective_actions.*.pic_user_id'],
                'corrective_actions.*.name'                => $allRules['corrective_actions.*.name'],
                'corrective_actions.*.due_date'            => $allRules['corrective_actions.*.due_date'],

                // Tambahkan validasi untuk hirarki kontrol dan tgl realisasi jika perlu
                'corrective_actions.*.control_hierarchy'   => 'required',
                'corrective_actions.*.actual_completion_date' => 'nullable|date',
            ],

            8 => [
                'key_learning' => $allRules['key_learning'],
            ],

            9 => array_merge([
                'penerimaan_komentar_internal_id'   => $allRules['penerimaan_komentar_internal_id'],
                'penerimaan_komentar_ohs_id'        => $allRules['penerimaan_komentar_ohs_id'],
                'penerimaan_komentar_internal'      => $allRules['penerimaan_komentar_internal'],
                'penerimaan_komentar_ohs'           => $allRules['penerimaan_komentar_ohs'],
            ], $kttRules, $contractorRules),
        ];

        // 4. Jalankan Validasi berdasarkan step saat ini
        if (isset($stepRules[$step])) {
            return $this->validate($stepRules[$step]);
        }
    }
    // app/Livewire/Incident/Update.php

    public function getCanUpdateProperty()
    {
        $user = Auth::user();
        $incident = $this->incident;

        // --- TAHAP 1: CEK POLICY (Siapa yang boleh klik?) ---

        // Step 1-2: Pelapor & Investigator
        if (in_array($this->currentStep, [1, 2])) {
            if (!$user->can('updateInitialData', $incident)) return false;
        }

        // Step 3-6 & 8: Khusus Investigator
        if (in_array($this->currentStep, [3, 4, 5, 6, 8])) {
            if (!$user->can('conductInvestigation', $incident)) return false;
        }

        // Step 7: Investigator atau Assignee
        if ($this->currentStep == 7) {
            if (!$user->can('manageCorrectiveActions', $incident)) return false;
        }

        // Step 9: Management / Reviewer
        if ($this->currentStep == 9) {
            if (!$user->can('reviewReport', $incident)) return false;
        }

        // --- TAHAP 2: CEK VALIDASI KTT (Apakah data sudah lengkap?) ---

        // Khusus di Step 9, tambahkan interlock KTT untuk risiko tinggi
        // if ($this->currentStep == 9) {
        //     if ($this->contractor_id == null) {
        //         return !empty($this->penerimaan_komentar_contractor_id) && !empty($this->penerimaan_komentar_contractor);
        //     }
        //     if (in_array($this->rating_name, ['Sedang', 'Tinggi', 'Ekstrem'])) {
        //         return !empty($this->penerimaan_komentar_ktt_id) && !empty($this->penerimaan_komentar_ktt);
        //     }
        // }

        return true;
    }
    public function getFieldsForStep($step)
    {
        $map = [
            1 => ['event_type_id', 'event_sub_type_id', 'description', 'location_id', 'date_time', 'title'],
            2 => ['directly_involved'],
            3 => ['pemimpin', 'facilitator', 'anggota'],
            4 => ['peepo'],
            5 => ['why_analysis'],
            6 => ['unsafe_conditions', 'unsafe_acts', 'personal_factors', 'job_factors', 'control_system_factors'],
            7 => ['corrective_actions', 'visual_evidence', 'supporting_documents'],
            8 => ['key_learning'],
            9 => ['penerimaan_komentar_ohs', 'penerimaan_komentar_internal', 'penerimaan_komentar_contractor']
        ];

        return $map[$step] ?? [];
    }

    // Helper untuk judul step (biar rapi di tampilan)

    /**
     * Helper untuk dynamic why analysis rules
     */
    protected function getWhyAnalysisRules()
    {
        $rules = ['why_analysis' => 'required|array'];
        foreach (range(1, $this->whyCount ?? 5) as $i) {
            $rules["why_analysis.why{$i}"] = 'required|string|min:3';
        }
        return $rules;
    }
    public function getProgressPercentage()
    {
        // 1. Eager Load Counts & Relations untuk menghindari N+1 query dan data null
        $this->incident->loadCount([
            'involvedPersons',
            'investigationTeams',
            'correctiveActions',
            'timelines'
        ]);

        // Load relasi yang dibutuhkan untuk filter koleksi
        $this->incident->load(['peepoAnalyses', 'correctiveActions', 'timelines']);

        // 2. Definisikan kondisi otorisasi (Rating & Contractor)
        $requiresKTT = in_array($this->incident->rating_name, ['Sedang', 'Tinggi', 'Ekstrem']);
        $hasContractor = !empty($this->incident->contractor_id);

        $steps = [
            'step1' => !empty($this->incident->event_type_id),

            // Step 2 & 3: Menggunakan hasil loadCount
            'step2' => $this->incident->involved_persons_count > 0,
            'step3' => $this->incident->investigation_teams_count > 0,

            // Step 4: Memastikan ada temuan yang bukan strip atau kosong
            'step4' => $this->incident->peepoAnalyses
                ->whereNotNull('temuan')
                ->whereNotIn('temuan', ['-', '', 'null'])
                ->isNotEmpty(),

            // Step 5: Harus ada minimal satu timeline yang memiliki analisis root cause (why_count_used > 0)
            'step5' => $this->incident->timelines->where('why_count_used', '>', 0)->isNotEmpty(),

            // Step 6: Validasi SCAT (memastikan JSON/Array tidak hanya berisi string kosong)
            'step6' => !empty($this->incident->scat_analysis) &&
                collect($this->incident->scat_analysis)->flatten()->filter(fn($val) => !empty($val))->isNotEmpty(),

            // Step 7: Semua tindakan perbaikan HARUS memiliki tanggal penyelesaian (Actual Completion Date)
            'step7' => $this->incident->corrective_actions_count > 0 &&
                $this->incident->correctiveActions->whereNull('actual_completion_date')->isEmpty(),

            // Step 8: Key Learning dari database atau state saat ini
            'step8' => !empty($this->key_learning) || !empty($this->incident->key_learning),
        ];

        // --- Logika Step 9 (Otorisasi & Komentar) ---
        // OHS dan Internal OHS wajib bagi semua laporan
        $step9_OHS = !empty($this->penerimaan_komentar_ohs_id) && !empty($this->penerimaan_komentar_internal_id);
        // KTT wajib jika rating menengah ke atas
        $step9_KTT = $requiresKTT ? !empty($this->penerimaan_komentar_ktt_id) : true;
        // Vendor wajib jika laporan terkait kontraktor
        $step9_Vendor = $hasContractor ? !empty($this->penerimaan_komentar_contractor_id) : true;


        $steps['step9'] = $step9_OHS && $step9_KTT && $step9_Vendor;

        // --- Kalkulasi Akhir ---
        $completedCount = collect($steps)->filter(fn($val) => $val === true)->count();
        $totalSteps = count($steps);

        return (int) round(($completedCount / $totalSteps) * 100);
    }
    public function isFieldInStep($step, $errorFields)
    {
        // Ambil semua key field yang sedang error
        $fields = array_keys($errorFields);

        foreach ($fields as $field) {
            switch ($step) {
                case 1:
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
                        'selectedBodyParts',
                        'selectedBodyParts.*', // Tambahkan ini juga di list field step 1
                        'damage_detail',
                        'incident_photo',
                        'incident_photo.*',
                        'saved_photos'
                    ];
                    if (in_array($field, $step1Fields)) return true;
                    break;

                case 2:
                    if (str_starts_with($field, 'directly_involved')) return true;
                    break;

                case 3:
                    if (collect(['pemimpin', 'facilitator', 'anggota'])->some(fn($p) => str_starts_with($field, $p))) return true;
                    break;

                case 4:
                    if (str_starts_with($field, 'peepo')) return true;
                    break;

                case 5:
                    if (str_starts_with($field, 'timelines')) return true;
                    break;

                case 6:
                    if (collect(['unsafe', 'personal_factors', 'job_factors', 'control_system_factors'])->some(fn($p) => str_starts_with($field, $p))) return true;
                    break;

                case 7:
                    if (collect(['visual_evidence', 'supporting_documents', 'corrective_actions'])->some(fn($p) => str_starts_with($field, $p))) return true;
                    break;

                case 8:
                    if ($field === 'key_learning') return true;
                    break;

                case 9:
                    if (str_starts_with($field, 'penerimaan_komentar')) return true;
                    break;
            }
        }

        return false;
    }
    // Logic di Class PHP Livewire
    /**
     * Hook ketika Kolom Kontraktor diubah
     */

    public function update()
    {
        $this->validateOnlyStep($this->currentStep);

        // Gunakan $this->incident agar sinkron dengan state komponen
        $report = $this->incident;

        // 1. Proteksi Race Condition
        if ($report->lock_version !== $this->current_lock_version) {
            $this->dispatch('alert', [
                'text' => "Data telah diperbarui oleh user lain (Versi Konflik). Silakan refresh.",
                'type' => 'error'
            ]);
            return;
        }


        try {
            DB::transaction(function () use ($report) {

                // 2. Update Involved Persons (Gunakan delete/create hanya jika data bersifat temporer)
                $report->involvedPersons()->delete();
                foreach ($this->directly_involved as $person) {
                    if (!empty($person['employee_name'])) {
                        $report->involvedPersons()->create([
                            'employee_id'      => $person['employee_id'] ?? null,
                            'employee_name'    => $person['employee_name'],
                            'employee_nik'     => $person['employee_nik'],
                            'dept_cont'        => $person['dept_cont'],
                            'jabatan'          => $person['jabatan'],
                            'roster'           => $person['roster'],
                            'shift'            => $person['shift'] ?? $person['sift'] ?? null,
                            'keterlibatan'     => $person['keterlibatan'],
                            'pengalaman_kerja' => $person['pengalaman_kerja'],
                        ]);
                    }
                }

                // 3. Update Investigation Teams
                $report->investigationTeams()->delete();
                $userIdsToNotify = [];

                foreach (['pemimpin', 'facilitator', 'anggota'] as $role) {
                    foreach ($this->{$role} as $member) {
                        // Cek apakah nama tidak kosong (berlaku untuk DB maupun Manual)
                        if (!empty($member['name'])) {

                            $report->investigationTeams()->create([
                                // Jika manual, user_id akan tetap null di database
                                'user_id' => $member['user_id'] ?? null,
                                'name'    => $member['name'],
                                'role'    => $role,
                                'dept'    => $member['dept'] ?? null,
                                'jabatan' => $member['jabatan'] ?? null,
                            ]);

                            // Hanya tambahkan ke array notifikasi jika user_id ada (User terdaftar di sistem)
                            if (!empty($member['user_id'])) {
                                $userIdsToNotify[] = $member['user_id'];
                            }
                        }
                    }
                }
                if (!$this->incident->investigationTeams()->exists()) {
                    $moderators = $this->incident->getAssignedModerators();
                    // 2. Kirim email ke masing-masing moderator
                    foreach ($moderators as $moderator) {
                        MailHelper::sendToUserId(
                            $moderator->id,
                            'Permintaan Penunjukan Tim Investigasi', // Subject
                            'emails.notification',
                            [
                                'subject'        => 'Penunjukan Tim Investigasi: ' . $this->incident->report_number,
                                'title'          => 'Permintaan Penunjukan Tim Investigasi',
                                'messageText'    => "Halo {$moderator->name},\n\nLaporan insiden baru dengan nomor {$this->incident->report_number} memerlukan perhatian Anda. Mohon segera menambahkan personil tim investigasi (Pemimpin, Fasilitator, dan Anggota) ke dalam laporan ini agar proses investigasi dapat segera dimulai.",
                                'additionalInfo' => "Nomor Laporan: {$this->incident->report_number}\nKlasifikasi: {$this->incident->eventType?->name}\nLokasi: {$this->incident->location?->location_name}\nStatus Saat Ini: {$this->status}",
                                'actionUrl'      => route('incident-detail', $this->incident->id)
                            ]
                        );
                    }
                }
                // 2. Kirim Email (Gunakan array_unique agar satu user tidak dapat 2 email jika dia punya double role)
                foreach (array_unique($userIdsToNotify) as $userId) {
                    MailHelper::sendToUserId(
                        $userId,
                        'Notifikasi Tim Investigasi Insiden',
                        'emails.notification',
                        [
                            'subject'        => 'Penugasan Tim Investigasi - ' . $report->report_number,
                            'title'          => 'Penugasan Tim Investigasi',
                            'messageText'    => "Anda telah ditunjuk sebagai bagian dari tim investigasi untuk laporan insiden {$report->report_number}.\nSilakan lakukan tinjauan dan analisis sesuai peran Anda.",
                            'additionalInfo' => "Nomor Laporan: {$report->report_number}\nStatus: {$this->status}",
                            'actionUrl'      => route('incident-detail', $report->id) // Sesuaikan nama route Anda
                        ]
                    );
                }

                // 4. Update PEEPO
                foreach ($this->peepo as $key => $rows) {

                    // Hapus data lama factor ini
                    $report->peepoAnalyses()
                        ->where('factor_key', $key)
                        ->delete();

                    // Insert ulang semua row
                    foreach ($rows as $row) {

                        // Skip row kosong
                        if (
                            empty($row['temuan']) &&
                            empty($row['deskripsi'])
                        ) {
                            continue;
                        }

                        $report->peepoAnalyses()->create([
                            'factor_key'  => $key,
                            'factor_name' => $this->peepoFactors[$key],
                            'temuan'      => $row['temuan'] ?? '-',
                            'deskripsi'   => $row['deskripsi'] ?? '-',
                        ]);
                    }
                }

                $whyCount = collect($this->why_analysis)->filter(fn($val) => !empty($val))->count();
                $report->timelines()->updateOrCreate(
                    ['incident_report_id' => $report->id],
                    ['analysis_steps' => $this->why_analysis, 'why_count_used' => $whyCount]
                );
                // ---------------------------------------------------------
                // 9. UPDATE ATTACHMENTS (APPEND MODE)
                // ---------------------------------------------------------

                // Simpan Visual Evidence Baru (Jika ada yang baru di-upload)
                if (!empty($this->visual_evidence_paths)) {
                    foreach ($this->visual_evidence_paths as $vPath) {
                        // Cek apakah path ini sudah ada di database untuk incident ini
                        // Ini mencegah duplikasi jika user menekan tombol simpan berkali-kali
                        $report->attachments()->firstOrCreate([
                            'file_path' => $vPath,
                            'file_type' => 'visual',
                        ], [
                            'file_name' => basename($vPath),
                        ]);
                    }
                    // Kosongkan array temporary paths setelah berhasil masuk ke DB
                    // agar tidak terproses ulang di klik simpan berikutnya
                    $this->visual_evidence_paths = [];
                    $this->visual_evidence = [];
                }
                if (!empty($this->saved_photos)) {
                    foreach ($this->saved_photos as $vPath) {
                        // Cek apakah path ini sudah ada di database untuk incident ini
                        // Ini mencegah duplikasi jika user menekan tombol simpan berkali-kali
                        $report->attachments()->firstOrCreate([
                            'file_path' => $vPath,
                            'file_type' => 'incident_photo',
                        ], [
                            'file_name' => basename($vPath),
                        ]);
                    }
                    // Kosongkan array temporary paths setelah berhasil masuk ke DB
                    // agar tidak terproses ulang di klik simpan berikutnya
                    $this->saved_photos = [];
                    $this->incident_photo = [];
                }

                // Simpan Supporting Documents Baru
                if (!empty($this->supporting_documents_paths)) {
                    foreach ($this->supporting_documents_paths as $dPath) {
                        $report->attachments()->firstOrCreate([
                            'file_path' => $dPath,
                            'file_type' => 'document',
                        ], [
                            'file_name' => basename($dPath),
                        ]);
                    }
                    $this->supporting_documents_paths = [];
                    $this->supporting_documents = [];
                }
                // 5. Update Corrective Actions
                $report->correctiveActions()->delete();
                foreach ($this->corrective_actions as $action) {
                    if (!empty($action['action_description'])) {
                        $report->correctiveActions()->create([
                            'action_description'     => $action['action_description'],
                            'hierarchy'              => $action['control_hierarchy'],
                            'pic_user_id'            => $action['pic_user_id'],
                            'name'            => $action['name'],
                            'due_date'               => $action['due_date'],
                            'actual_completion_date' => $action['actual_completion_date'] ?? null,
                            'status'                 => !empty($action['actual_completion_date']) ? 'Closed' : 'Open',
                        ]);
                    }
                }

                // 6. Tentukan Status Berdasarkan Data Terbaru
                // Penting: Pastikan determineReportStatus() membaca data dari $this
                // Hitung status terbaru
                $calculatedStatus = $this->determineReportStatus();

                // Simpan ke database (Hanya status utamanya saja)
                // explode(':') memastikan "In Progress : Teams" menjadi "In Progress"
                $cleanStatus = trim(explode(':', $calculatedStatus)[0]);
                // A. DETEKSI PERUBAHAN (Sebelum Update)
                $isContractorChanged = $report->contractor_id != (($this->deptCont === 'cont') ? $this->contractor_id : null);
                $isRatingChanged = $report->risk?->rating_name != $this->RiskAssessment?->name;

                // B. LOGIKA RESET APPROVAL
                if ($isContractorChanged) {
                    $this->penerimaan_komentar_contractor = null;
                    $this->penerimaan_komentar_contractor_id = null;
                }

                if ($isRatingChanged) {
                    // Jika rating berubah, tanda tangan KTT lama harus tidak valid
                    $this->penerimaan_komentar_ktt = null;
                    $this->penerimaan_komentar_ktt_id = null;
                }

                // C. UPDATE DATA UTAMA
                // 7. Update Data Utama & Increment Lock
                $report->update([
                    'status'                => $cleanStatus,
                    'title'                 => $this->title,
                    'event_type_id'         => $this->event_type_id,
                    'event_sub_type_id'     => $this->event_sub_type_id,
                    'tasks'                 => $this->tasks,
                    'potential_lti'         => $this->potential_lti,
                    'description'           => $this->description,
                    'date_time'             => $this->date_time,
                    'location_id'           => $this->location_id,
                    'location_specific'     => $this->location_specific,
                    'contract_area_name'    => $this->contract_area_name,
                    'env_classification'    => $this->env_classification,
                    'pelapor_id'            => $this->pelapor_id,
                    'user_auth'         => Auth::user()->id,
                    'manual_pelapor_name'   => $this->manualPelaporName,
                    'department_id'         => ($this->deptCont === 'dept') ? $this->department_id : null,
                    'contractor_id'         => ($this->deptCont === 'cont') ? $this->contractor_id : null,
                    'emergency_action'      => $this->emergency_action,
                    'penanggung_jawab'      => $this->penanggungJawab,
                    'key_learning'          => $this->key_learning,
                    'scat_analysis'         => [
                        'langsung' => ['kondisi_tidak_aman' => $this->unsafe_conditions, 'perilaku_tidak_aman' => $this->unsafe_acts],
                        'dasar'    => ['faktor_pribadi' => $this->personal_factors, 'faktor_pekerjaan' => $this->job_factors, 'sistem_kontrol' => $this->control_system_factors],
                    ],
                    'pm_contractor_comment' => $this->contractor_id ? $this->penerimaan_komentar_contractor : null,
                    'pm_contractor_id'      => $this->contractor_id ? $this->penerimaan_komentar_contractor_id : null,
                    'pm_internal_comment'   => $this->penerimaan_komentar_internal,
                    'pm_internal_id'        => $this->penerimaan_komentar_internal_id,
                    'ohs_head_comment'      => $this->penerimaan_komentar_ohs,
                    'ohs_head_id'           => $this->penerimaan_komentar_ohs_id,
                    'ktt_comment'           => in_array($this->rating_name, ['Sedang', 'Tinggi', 'Ekstrem']) ? $this->penerimaan_komentar_ktt : null,
                    'ktt_id'                => in_array($this->rating_name, ['Sedang', 'Tinggi', 'Ekstrem']) ? $this->penerimaan_komentar_ktt_id : null,
                    'lock_version'          => $report->lock_version + 1, // Increment manual di dalam transaksi
                ]);

                // 8. Risk Assessment
                $report->risk()->updateOrCreate(
                    ['incident_report_id' => $report->id],
                    [
                        'likelihood_id'  => $this->likelihood_id,
                        'consequence_id' => $this->consequence_id,
                        'rating_name'    => $this->RiskAssessment?->name,
                    ]
                );
                $report->impact->update([
                    'is_injury' => $this->isInjury,
                    'damage_detail' => !$this->isInjury ? $this->damage_detail : null,
                ]);

                if ($this->isInjury) {
                    // sync() akan menghapus yang lama dan memasukkan yang baru dipilih
                    $report->impact->bodyParts()->sync($this->selectedBodyParts);
                } else {
                    $report->impact->bodyParts()->detach();
                }
            });
            $this->status = $this->determineReportStatus();
            // 9. Post-Success Synchronization
            $this->incident->refresh();
            $allFiles = $this->incident->attachments;
            $this->existing_visual_evidence = $allFiles->where('file_type', 'visual')->values()->all();
            $this->existing_saved_photos = $allFiles->where('file_type', 'incident_photo')->values()->all();
            $this->existing_supporting_documents = $allFiles->where('file_type', 'document')->values()->all();
            $this->current_lock_version = $this->incident->lock_version;

            if ($this->currentStep < 9) {
                $this->currentStep++;
            }

            $this->dispatch('alert', [
                'text' => "SENTRY: Data berhasil diperbarui. Status: {$this->status}",
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            // \Log::error("Update Incident Failed: " . $e->getMessage());
            $this->dispatch('alert', [
                'text' => "Update Incident Failed: " . $e->getMessage(),
                'type' => 'error'
            ]);
        }
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
    #[Computed]
    public function isEnvironmentType()
    {
        // Cari nama event type berdasarkan ID yang sedang dipilih user
        $selectedType = EventType::where('id', $this->event_type_id)->first();


        // Pastikan pengecekan string sesuai dengan data di database Anda (misal: 'Lingkungan' atau 'Environment')
        return $selectedType && ($selectedType['event_type_name'] === 'Lingkungan' || $selectedType['event_type_name'] === 'Environment');
    }

    // app/Livewire/IncidentReport.php

    public function sendAlert()
    {
        // Ambil data incident
        $incident = IncidentReport::with('location', 'department', 'contractor', 'attachments')
            ->findOrFail($this->incidentId);

        // Filter foto (Incident Photo)
        $visualPhotos = $incident->attachments->where('file_type', 'incident_photo')
            ->take(2)
            ->map(function ($doc) {
                return [
                    'full_path' => storage_path('app/public/' . $doc->file_path),
                    'exists'    => file_exists(storage_path('app/public/' . $doc->file_path))
                ];
            });

        $datetime = \Carbon\Carbon::parse($incident->date_time);

        $data = [
            'safety_no'         => $incident->report_number,
            'inx_no'            => $incident->report_number,
            'date'              => $datetime->format('d/m/Y'),
            'time'              => $datetime->format('H:i'),
            'location'          => $incident->location->name ?? '-',
            'department'        => $incident->department->department_name ?? ($incident->contractor->contractor_name ?? '-'),
            'description'       => $incident->description,
            'immediate_actions' => $incident->emergency_action,
            'approver_name'     => $this->name_ktt, // Sesuaikan
            'approval_date'     => now()->format('d/m/Y'),
            'contact_person'    => auth()->user()->name,
            'contact_date'      => now()->format('d/m/Y'),
            'photos'            => $visualPhotos,
        ];

        // Kirim Email
        Mail::to('yoman.banea@archimining.com')->send(new IncidentAlertMail($data));

        $this->dispatch('alert', [
            'text' => "Alert has been sent successfully!",
            'type' => 'success'
        ]);
    }
    public $previewData = [];
    public function showPreview($id)
    {
        $incident = IncidentReport::with('location', 'department', 'contractor', 'attachments')->find($id);

        $incident = IncidentReport::with('location', 'department', 'contractor', 'attachments')->whereId($this->incidentId)->first();

        $visualPhotos = $incident->attachments->where('file_type', 'incident_photo')
            ->take(2)
            ->map(function ($doc) {
                return [
                    'full_path' => storage_path('app/public/' . $doc->file_path),
                    'exists'    => file_exists(storage_path('app/public/' . $doc->file_path))
                ];
            });

        $datetime = Carbon::parse($incident->date_time);
        $data = [
            'safety_no' => $incident->report_number,
            'inx_no' => $incident->report_number,
            'date'      => $datetime->format('d/m/Y'),
            'time'      => $datetime->format('H:i'),
            'location' => $incident->location->name,
            'department' => $incident->department->department_name ?? $incident->contractor->contractor_name,
            'description' => $incident->description,
            'immediate_actions' => $incident->emergency_action,
            'approver_name' => 'Nama Pejabat',
            'approval_date' => now()->format('d/m/Y'),
            'contact_person' => Auth::user()->mail,
            'contact_date' => now()->format('d/m/Y'),
            // Pastikan path foto adalah path absolut di server
            'photos' => $visualPhotos,
        ];


        $this->previewData = $data; // Simpan ke public property
    }


    public function addRowPeepo($factor)
    {
        $this->peepo[$factor][] = [
            'temuan' => '',
            'deskripsi' => '',
        ];
    }

    public function removeRowPeepo($factor, $index)
    {
        unset($this->peepo[$factor][$index]);

        $this->peepo[$factor] = array_values($this->peepo[$factor]);
    }
}
