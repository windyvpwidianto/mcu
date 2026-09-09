<?php

namespace App\Livewire\Hazard;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Hazard;
use Livewire\Component;
use App\Models\Location;
use App\Models\EventType;
use App\Models\UnsafeAct;
use App\Models\Contractor;
use App\Models\Department;
use App\Models\Likelihood;
use App\Helpers\FileHelper;
use App\Models\ActionHazard;
use App\Models\EventSubType;
use App\Models\ErmAssignment;
use Livewire\WithFileUploads;
use App\Models\RiskAssessment;
use App\Models\RiskMatrixCell;
use App\Models\RiskConsequence;
use App\Models\UnsafeCondition;
use Livewire\Attributes\Validate;
use App\Models\RiskAssessmentMatrix;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DateBeforeOrEqualToday;
use App\Services\HazardService;

class HazardForm extends Component
{
    use WithFileUploads;
    // field tambahan tanpa aturan validasi
    public $deptCont = 'department'; // default departemen
    public $searchLocation = '';
    public $searchPelapor = '';
    public $searchActResponsibility = '';
    public $locations = [];
    public $pelapors = [];
    public $search = '';
    public $departments = [];
    public $showDropdown = false;
    public $show_location = false;
    public $showPelaporDropdown = false;
    public $pelaporsAct = [];
    public $showActPelaporDropdown = false;
    public $searchContractor = '';
    public $contractors = [];
    public $showContractorDropdown = false;
    public $penanggungJawabOptions = [];
    public $likelihoods, $consequences;
    public $selectedLikelihoodId = null;
    public $selectedConsequenceId = null;
    public $status;
    public $action_final_doc;
    public $RiskAssessment;
    #[Validate('required')]
    public $likelihood_id;
    #[Validate('required')]
    public $consequence_id;
    #[Validate('required')]
    public $location_id;
    #[Validate]
    public $pelapor_id;
    #[Validate('required|string')]
    public $description;
    #[Validate('required|string')]
    public $immediate_corrective_action;
    #[Validate('required_without:contractor_id')]
    public $department_id;
    #[Validate('required_without:department_id')]
    public $contractor_id;
    #[Validate('required|string')]
    public $penanggungJawab;
    #[Validate('nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx')]
    public $doc_deskripsi;
    #[Validate('nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx')]
    public $doc_corrective;
    #[Validate('required|string')]
    public $tipe_bahaya;
    #[Validate('required|string')]
    public $sub_tipe_bahaya;
    #[Validate('required|string')]
    public $location_specific;

    #[Validate('required')]
    public $keyWord = 'kta';
    #[Validate('required_without:tindakan_tidak_aman')]
    public $kondisi_tidak_aman;
    #[Validate('required_without:kondisi_tidak_aman')]
    public $tindakan_tidak_aman;
    #[Validate(['required', 'date', new DateBeforeOrEqualToday])]
    public $tanggal;
    public $manualPelaporMode = false;
    public $manualPelaporName = '';
    public $manualActPelaporMode = false;
    public $manualActPelaporName = '';
    // input action
    public $action_description;
    public $action_responsible_id;
    public $action_due_date;
    public $actual_close_date;
    // Menyimpan sementara object file upload dari Livewire (TemporaryUploadedFile)
    // File TIDAK langsung disimpan ke disk. Penyimpanan permanen hanya terjadi saat submit().
    public $doc_deskripsi_temp  = null;
    public $doc_corrective_temp = null;
    public $actions = []; // kumpulan action sebelum disimpan
    public $showActionModal = 'close'; // default tertutup

    // Fungsi untuk memastikan modal terbuka/tertutup dengan benar
    public function updatedShowActionModal($value)
    {
        if ($value === 'open') {
            // Bisa tambahkan logic reset form input disini jika perlu
        }
    }
    public function rules()
    {
        $baseRules = [

            'pelapor_id' => $this->manualPelaporMode ? 'nullable' : 'required',
            'manualPelaporName' => $this->manualPelaporMode ? 'required|string|max:255' : 'nullable',
        ];
        if (!empty($this->action_description)) {
            $baseRules['action_description'] = 'required'; // Ubah format jika berbeda
            $baseRules['action_due_date'] = 'required|date_format:d-m-Y';
            $baseRules['action_responsible_id'] = 'required|exists:users,id';
            // Tambahkan rule lain yang harus required
        } else {
            // Jika action_description kosong, field ini boleh null/kosong
            $baseRules['action_due_date'] = 'nullable|date_format:d-m-Y';
            $baseRules['action_responsible_id'] = 'nullable|exists:users,id';
        }
        return $baseRules;
    }
    protected $messages = [

        'likelihood_id.required'     => 'likelihood wajib diisi.',
        'consequence_id.required'     => 'consequence wajib diisi.',
        'location_id.required'     => 'Lokasi wajib diisi.',
        'location_specific.required'     => 'Lokasi Spesifik wajib diisi.',

        'description.required'     => 'Deskripsi wajib diisi.',
        'description.string'       => 'Deskripsi harus berupa teks.',

        'immediate_corrective_action.required'     => 'Tindakan perbaikan langsung wajib diisi.',
        'immediate_corrective_action.string'       => 'Tindakan perbaikan langsung harus berupa teks.',

        'department_id.required_without' => 'Departemen wajib dipilih jika kontraktor tidak diisi.',
        'contractor_id.required_without' => 'Kontraktor wajib dipilih jika departemen tidak diisi.',

        'kondisi_tidak_aman.required_without' => 'Kondisi Tidak Aman wajib dipilih jika Tindakan Tidak Aman tidak diisi.',
        'tindakan_tidak_aman.required_without' => 'Tindakan Tidak Aman wajib dipilih jika Kondisi Tidak Aman tidak diisi.',

        'pelapor_id.required' => 'Pelapor wajib dipilih.',
        'penanggungJawab.required' => 'Penanggung jawab area wajib dipilih.',
        'penanggungJawab.string'   => 'Penanggung jawab harus berupa teks.',

        'tipe_bahaya.required'     => 'Tipe Bahaya wajib dipilih.',
        'tipe_bahaya.string'       => 'Tipe Bahaya harus berupa teks.',

        'sub_tipe_bahaya.required' => 'Sub Tipe Bahaya wajib dipilih.',
        'sub_tipe_bahaya.string'   => 'Sub Tipe Bahaya harus berupa teks.',

        'tanggal.required'         => 'Tanggal wajib dipilih.',
        'tanggal.date'             => 'Tanggal harus berupa format tanggal valid.',
        'doc_deskripsi.file'   => 'File deskripsi harus berupa berkas yang valid.',
        'doc_deskripsi.mimes'  => 'File deskripsi hanya boleh dalam format JPG, JPEG, PNG, atau PDF.',
        'doc_deskripsi.max'    => 'Ukuran file deskripsi maksimal 2 MB.',

        'doc_corrective.file'  => 'File tindakan perbaikan harus berupa berkas yang valid.',
        'doc_corrective.mimes' => 'File tindakan perbaikan hanya boleh dalam format JPG, JPEG, PNG, DOC, DOCX atau PDF.',
        'doc_corrective.max'   => 'Ukuran file tindakan perbaikan maksimal 2 MB.',
    ];

    public function getHasWhatErrorProperty()
    {
        return $this->getErrorBag()->has('tipe_bahaya') || $this->getErrorBag()->has('sub_tipe_bahaya') || ($this->keyWord === 'kta' && $this->getErrorBag()->has('kondisi_tidak_aman')) || ($this->keyWord === 'tta' && $this->getErrorBag()->has('tindakan_tidak_aman'));
    }
    public function getHasHowErrorProperty()
    {
        return $this->getErrorBag()->has('immediate_corrective_action') || $this->getErrorBag()->has('penanggungJawab') || ($this->deptCont === 'department' && $this->getErrorBag()->has('department_id')) || ($this->deptCont === 'company' && $this->getErrorBag()->has('contractor_id'));
    }
    public function getHasWhereErrorProperty()
    {
        return $this->getErrorBag()->has('location_id') || $this->getErrorBag()->has('location_specific');
    }
    public function getHasWhyErrorProperty()
    {
        return $this->getErrorBag()->has('description');
    }
    public function getHasWhenErrorProperty()
    {
        return $this->getErrorBag()->has('tanggal');
    }
    public function getHasWhoErrorProperty()
    {
        return $this->getErrorBag()->has('pelapor_id');
    }
    public function mount()
    {
        if (Auth::check()) {
            $this->pelapor_id = Auth::id();
            $this->searchPelapor = Auth::user()->name;
        }
        $this->likelihoods = Likelihood::orderByDesc('level')->get();
        $this->consequences = RiskConsequence::orderBy('level')->get();
    }
    public function uploadImage()
    {
        if (request()->hasFile('upload')) {
            $file     = request()->file('upload');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs('uploads/ckeditor', $filename, 'public');

            return response()->json([
                'url' => asset('storage/' . $path),
            ]);
        }
    }

    // Hook untuk doc_deskripsi
    // File hanya disimpan ke temporary Livewire storage. Kompresi & pemindahan
    // ke folder permanen dilakukan oleh HazardService saat submit() dipanggil.
    public function updatedDocDeskripsi()
    {
        $this->validate(['doc_deskripsi' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240']);
        // Simpan objek file sementara untuk diproses oleh service saat submit
        $this->doc_deskripsi_temp = $this->doc_deskripsi;
    }

    // Hook untuk doc_corrective
    public function updatedDocCorrective()
    {
        $this->validate(['doc_corrective' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240']);
        // Simpan objek file sementara untuk diproses oleh service saat submit
        $this->doc_corrective_temp = $this->doc_corrective;
    }

    // Menangkap data dari CKEditor 'action_description'

    public function updated($propertyName)
    {
        $fieldsToValidate = [
            'location_id',
            'location_specific',
            'description',
            'severity',
            'department_id',
            'contractor_id',
            'penanggungJawab',
            'tanggal',
            'description',
            'action_description'
        ];

        if (in_array($propertyName, $fieldsToValidate)) {
            $this->validateOnly($propertyName);
        }
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
    public function updatedKeyWord($value)
    {
        if ($value === 'kta') {
            $this->resetErrorBag(['tindakan_tidak_aman']);
            $this->reset(['tindakan_tidak_aman']);
        } elseif ($value === 'tta') {
            $this->resetErrorBag(['kondisi_tidak_aman']);
            $this->reset(['kondisi_tidak_aman']);
        }
    }
    public function updatedSearch()
    {
        if (strlen($this->search) > 1) {
            $this->departments = Department::where('department_name', 'like', '%' . $this->search . '%')
                ->orderBy('department_name')
                ->limit(80)
                ->get();
            $this->showDropdown = true;
        } else {
            $this->departments = [];
            $this->showDropdown = false;
        }
    }
    public function selectDepartment($id, $name)
    {
        $this->reset('searchContractor', 'contractor_id');
        $this->department_id = $id;
        $this->search = $name;
        $this->showDropdown = false;

        // Ambil user dari erm_assignments berdasarkan department_id
        $this->penanggungJawabOptions = ErmAssignment::where('department_id', $id)
            ->with('user:id,name')   // pastikan relasi user() ada di model
            ->get()
            ->pluck('user')
            ->filter()
            ->toArray();
        $this->validateOnly('department_id');
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
        $this->reset('search', 'department_id');
        $this->contractor_id = $id;
        $this->searchContractor = $name;
        $this->showContractorDropdown = false;
        // Ambil user dari erm_assignments berdasarkan contractor_id
        $this->penanggungJawabOptions = ErmAssignment::where('contractor_id', $id)
            ->with('user:id,name')
            ->get()
            ->pluck('user')
            ->filter()
            ->toArray();
        $this->validateOnly('contractor_id');
    }
    public function updatedSearchLocation()
    {
        if (strlen($this->searchLocation) > 2) {
            $this->locations = Location::where('name', 'like', '%' . $this->searchLocation . '%')
                ->orderBy('name')
                ->limit(10)
                ->get();
            $this->show_location = true;
        } else {
            $this->locations = [];
            $this->show_location = false;
        }
    }
    public function selectLocation($id, $name)
    {
        $this->location_id = $id;
        $this->searchLocation = $name;
        $this->show_location = false;
        $this->validateOnly('location_id');
    }
    public function updatedSearchPelapor()
    {
        $this->reset('manualPelaporName');
        if (strlen($this->searchPelapor) > 1) {
            $this->pelapors = User::where('name', 'like', '%' . $this->searchPelapor . '%')
                ->orderBy('name')
                ->limit(50)
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
        $this->manualPelaporMode = false;
        $this->validateOnly('pelapor_id');
    }
    public function enableManualPelapor()
    {
        $this->manualPelaporMode = true;
        $this->manualPelaporName = $this->searchPelapor; // isi default sama dengan isi search
        $this->showPelaporDropdown = false;
        $this->pelapor_id = null;
        $this->dispatch(
            'alert',
            [
                'text' => "nama sudah di tambahkan!!!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]
        );
    }
    public function updatedManualPelaporName($value)
    {
        $this->pelapor_id = null;
    }

    public function updatedSearchActResponsibility()
    {
        $this->reset('manualActPelaporName');
        $this->manualActPelaporMode = false;
        if (strlen($this->searchActResponsibility) > 1) {
            $this->pelaporsAct = User::where('name', 'like', '%' . $this->searchActResponsibility . '%')
                ->orderBy('name')
                ->limit(50)
                ->get();
            $this->showActPelaporDropdown = true;
        } else {
            $this->pelaporsAct = [];
            $this->showActPelaporDropdown = false;
        }
    }
    public function selectActPelapor($id, $name)
    {
        $this->action_responsible_id = $id;
        $this->searchActResponsibility = $name;
        $this->showActPelaporDropdown = false;
        $this->manualActPelaporMode = false;
        $this->validateOnly('action_responsible_id');
    }
    public function enableManualActPelapor()
    {
        $this->manualActPelaporMode = true;
        $this->manualActPelaporName = $this->searchPelapor; // isi default sama dengan isi search
    }
    public function updatedManualActPelaporName($value)
    {
        $this->action_responsible_id = null;
    }

    public function addActPelaporManual()
    {
        $this->searchActResponsibility = $this->manualActPelaporName;
        $this->showActPelaporDropdown = false;
        $this->action_responsible_id = null;
    }
    public function getIsFormValidProperty()
    {
        // Kalau user pilih department_id atau contractor_id salah satu boleh
        $validCompanyDept = $this->department_id || $this->contractor_id;
        return $validCompanyDept
            && !empty($this->location)
            && !empty($this->description)
            && !empty($this->severity);
    }
    public function addAction()
    {
        $this->dispatch('validate-action_description');

        $this->validate(
            [
                'action_description'       => 'required|string',
                'action_responsible_id'    => 'nullable|integer',
                // Tambahkan validasi untuk file upload
                'action_final_doc'         => 'nullable|file|max:5120', // Maksimal 5MB (opsional)

                // Due date harus sebelum atau sama dengan actual close date (jika close date ada)
                'action_due_date'       => [
                    'nullable',
                    'date_format:d-m-Y',
                    'before_or_equal:actual_close_date'
                ],

                // Actual close date harus sesudah atau sama dengan due date
                'actual_close_date' => [
                    'nullable',
                    'date_format:d-m-Y',
                    'after_or_equal:action_due_date'
                ],
            ],
            [
                'action_description.required'  => 'Deskripsi tindakan wajib diisi.',
                'action_due_date.required'     => 'Tanggal batas waktu wajib diisi.',
                'action_due_date.date_format'  => 'Format tanggal harus dd-mm-YYYY.',
                'action_due_date.before_or_equal' => 'Tanggal batas waktu tidak boleh melampaui tanggal penyelesaian.',

                'actual_close_date.date_format'    => 'Format tanggal harus dd-mm-YYYY.',
                'actual_close_date.after_or_equal' => 'Tanggal penyelesaian tidak boleh lebih kecil dari tanggal batas waktu.',

                'action_responsible_id.required' => 'Penanggung jawab wajib dipilih.',
                'action_final_doc.max'           => 'Ukuran dokumen maksimal adalah 5MB.',
            ]
        );

        // === LOGIKA UPLOAD FILE BARU ===
        $finalDocPaths = []; // Default array kosong
        if ($this->action_final_doc) {
            // Simpan file secara fisik ke storage menggunakan FileHelper
            $path = FileHelper::compressAndStore($this->action_final_doc, 'action_hazard_docs');

            // Masukkan ke dalam array karena tipe data database adalah JSON/Array
            $finalDocPaths[] = $path;
        }
        // ===============================

        // Masukkan data ke array sementara
        $this->actions[] = [
            'description'       => $this->action_description,
            'due_date'          => $this->action_due_date,
            'actual_close_date' => $this->actual_close_date,
            'responsible_id'    => $this->action_responsible_id,
            'final_doc'         => $finalDocPaths, // <-- Masukkan path file di sini
        ];
        $this->dispatch('alert', [
            'text' => "Tindakan Lanjutan berhasil dibuat!!",
            'duration' => 5000,
            'destination' => '/contact',
            'newWindow' => true,
            'close' => true,
            'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
        ]);

        // reset input sementara, tambahkan 'action_final_doc' agar input file bersih kembali
        $this->reset([
            'action_description',
            'action_due_date',
            'action_responsible_id',
            'actual_close_date',
            'searchActResponsibility',
            'action_final_doc' // <-- Reset property file
        ]);

        $this->dispatch('reset-editor-action_description');
        // $this->dispatch('reset-ckeditor');
    }
    public function removeAction($index)
    {
        unset($this->actions[$index]);
        $this->actions = array_values($this->actions); // reindex
        $this->dispatch(
            'alert',
            [
                'text' => "Tindakan Lanjutan berhasil di hapus!!!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #ff3333, #ff6666)",
            ]
        );
    }
    public function submit()
    {
        $this->dispatch('validate-description');
        $this->dispatch('validate-immediate_corrective_action');

        $this->validate();

        // Peringatan: ada action yang belum di-klik TAMBAH
        if (!empty($this->action_description)) {
            $this->dispatch('alert', [
                'text'            => "Anda sudah mengisi Tindakan Lanjutan tetapi belum mengklik tombol TAMBAH!",
                'duration'        => 6000,
                'close'           => true,
                'backgroundColor' => "linear-gradient(to right, #ff3333, #ff6666)",
            ]);
            return;
        }

        // Delegasikan semua business logic ke HazardService
        app(HazardService::class)->store(
            data: [
                'tipe_bahaya'                 => $this->tipe_bahaya,
                'sub_tipe_bahaya'             => $this->sub_tipe_bahaya,
                'department_id'               => $this->department_id,
                'contractor_id'               => $this->contractor_id,
                'pelapor_id'                  => $this->pelapor_id,
                'manualPelaporName'           => $this->manualPelaporName,
                'penanggungJawab'             => $this->penanggungJawab,
                'location_id'                 => $this->location_id,
                'location_specific'           => $this->location_specific,
                'tanggal'                     => $this->tanggal,
                'description'                 => $this->description,
                'doc_deskripsi_temp'          => $this->doc_deskripsi_temp,
                'immediate_corrective_action' => $this->immediate_corrective_action,
                'doc_corrective_temp'         => $this->doc_corrective_temp,
                'keyWord'                     => $this->keyWord,
                'kondisi_tidak_aman'          => $this->kondisi_tidak_aman,
                'tindakan_tidak_aman'         => $this->tindakan_tidak_aman,
                'consequence_id'              => $this->consequence_id,
                'likelihood_id'               => $this->likelihood_id,
            ],
            actions: $this->actions
        );

        $this->dispatch('alert', [
            'text'            => "Laporan berhasil dikirim!",
            'duration'        => 5000,
            'destination'     => '/contact',
            'newWindow'       => true,
            'close'           => true,
            'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
        ]);

        $this->resetForm();
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

    public function render()
    {
        return view('livewire.hazard.hazard-form', [
            'users' => User::limit(10)->get(),
            'Department'   => Department::all(),
            'Contractors'  => Contractor::all(),
            'likelihoodss' => Likelihood::orderByDesc('level')->get(),
            'consequencess' => RiskConsequence::orderBy('level')->get(),
            'ktas' => UnsafeCondition::latest()->get(),
            'ttas' => UnsafeAct::latest()->get(),
            'eventTypes' => EventType::where('event_type_name', 'like', '%' . 'hazard' . '%')->get(),
            'subTypes' => EventSubType::where('event_type_id', $this->tipe_bahaya)->get()

        ]);
    }
    public function resetForm()
    {
        $this->reset([
            'tipe_bahaya',
            'sub_tipe_bahaya',
            'status',
            'department_id',
            'contractor_id',
            'pelapor_id',
            'penanggungJawab',
            'location_id',
            'location_specific',
            'tanggal',
            'description',
            'doc_deskripsi',
            'doc_deskripsi_temp',
            'immediate_corrective_action',
            'doc_corrective',
            'doc_corrective_temp',
            'keyWord',
            'kondisi_tidak_aman',
            'tindakan_tidak_aman',
            'consequence_id',
            'likelihood_id',
            'actions',
        ]);
        $this->dispatch('reset-all-editors');
    }
}
