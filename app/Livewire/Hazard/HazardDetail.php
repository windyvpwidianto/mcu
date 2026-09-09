<?php

namespace App\Livewire\Hazard;

use App\Enums\HazardStatus;
use App\Helpers\FileHelper;
use App\Helpers\MailHelper;
use App\Models\ActionHazard;
use App\Models\Contractor;
use App\Models\Department;
use App\Models\ErmAssignment;
use App\Models\EventSubType;
use App\Models\EventType;
use App\Models\Hazard;
use App\Models\HazardChat;
use App\Models\HazardWorkflow;
use App\Models\Likelihood;
use App\Models\Location;
use App\Models\ModeratorAssignment;
use App\Models\RiskAssessment;
use App\Models\RiskAssessmentMatrix;
use App\Models\RiskConsequence;
use App\Models\RiskMatrixCell;
use App\Models\UnsafeAct;
use App\Models\UnsafeCondition;
use App\Models\User;
use App\Notifications\HazardSubmittedNotification;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class HazardDetail extends Component
{
    use WithFileUploads, AuthorizesRequests;
    public $hazard;

    public string $proceedTo = '';
    public array $availableTransitions = [];
    public string $effectiveRole = '';
    public ?int $assignTo1 = null;
    public ?int $assignTo2 = null;
    public array $ermList = [];
    // Untuk menampung file baru dari input
    public $edit_action_final_doc;

    // Untuk menampung array file lama dari database
    public $existing_final_docs = [];
    public $asModerator = false;
    public $asErm = false;

    // Data dari Form
    // field tambahan tanpa aturan validasi
    public $deptCont = 'department'; // default departemen
    public $search = '';
    public $searchLocation = '';
    public $searchPelapor = '';
    public $searchActResponsibility = '';
    public $locations = [];
    public $pelapors = [];
    public $pelaporsAct = [];
    public $departments = [];
    public $showDropdown = false;
    public $showLocationDropdown = false;
    public $showPelaporDropdown = false;
    public $showActPelaporDropdown = false;
    public $searchContractor = '';
    public $contractors = [];
    public $showContractorDropdown = false;
    public $penanggungJawabOptions = [];
    public $likelihoods, $consequences;
    public $selectedLikelihoodId = null;
    public $selectedConsequenceId = null;
    public $RiskAssessment;
    public $status;
    #[Validate('required')]
    public $likelihood_id;
    #[Validate('required')]
    public $consequence_id;
    #[Validate('required')]
    public $location_id;
    #[Validate]
    public $pelapor_id;
    #[Validate('required')]
    public $description;
    #[Validate('required')]
    public $immediate_corrective_action;
    #[Validate('required_without:contractor_id')]
    public $department_id;
    #[Validate('required_without:department_id')]
    public $contractor_id;
    #[Validate('required')]
    public $penanggungJawab;
    #[Validate('nullable|file|mimes:jpg,jpeg,png,pdf,svg,doc,docx,xls,xlsx,csv|max:2048')]
    public $new_doc_deskripsi;
    public $doc_deskripsi;
    #[Validate('nullable|file|mimes:jpg,jpeg,png,pdf,svg,doc,docx,xls,xlsx,csv|max:2048')]
    public $new_doc_corrective;
    public $doc_corrective;
    #[Validate('required')]
    public $tipe_bahaya;
    #[Validate('required')]
    public $sub_tipe_bahaya;
    #[Validate('required')]
    public $location_specific;

    #[Validate('required')]
    public $keyWord = 'kta';
    #[Validate('required_without:tindakan_tidak_aman')]
    public $kondisi_tidak_aman;
    #[Validate('required_without:kondisi_tidak_aman')]
    public $tindakan_tidak_aman;
    #[Validate('required|date')]
    public $tanggal;
    public $manualPelaporMode = false;
    public $manualPelaporName = '';
    public $manualActPelaporMode = false;
    public $manualActPelaporName = '';
    public $hazard_id;
    public $newMessage;

    // Data Action Hazard
    public $action_description;

    #[Validate('nullable')]
    public $action_responsible_id;

    #[Validate([
        'nullable',
        'date_format:d-m-Y',
        'before_or_equal:action_actual_close_date'
    ], message: [
        'date_format' => 'Format tanggal harus dd-mm-YYYY.',
        'before_or_equal' => 'Tanggal batas waktu tidak boleh melampaui tanggal penyelesaian.'
    ])]
    public $action_due_date;
    #[Validate([
        'nullable',
        'date_format:d-m-Y',
        'after_or_equal:action_due_date'
    ], message: [
        'date_format' => 'Format tanggal harus dd-mm-YYYY.',
        'after_or_equal' => 'Tanggal penyelesaian tidak boleh lebih kecil dari tanggal batas waktu.'
    ])]
    public $action_actual_close_date;

    public $manualActPelaporModeEdit = false;
    public $showActPelaporDropdownEdit = false;
    public $pelaporsActEdit = [];
    public $searchActResponsibilityEdit = '';
    public $manualActPelaporNameEdit = '';

    public $edit_action_id;
    public $moderator_comment;
    public $edit_action_description;
    public $edit_action_due_date;
    public $edit_action_actual_close_date;
    public $edit_action_responsible_id;
    public $edit_searchResponsibility;
    public $audit_name;

    // Untuk menampilkan daftar ActionHazard terkait hazard
    public $actionHazards = [];

    public function rules()
    {
        return [
            'pelapor_id' => $this->manualPelaporMode ? 'nullable' : 'required',
            'manualPelaporName' => $this->manualPelaporMode ? 'required|string|max:255' : 'nullable',
        ];
    }
    protected $messages = [
        'likelihood_id.required'     => 'likelihood wajib diisi.',
        'consequence_id.required'     => 'consequence wajib diisi.',
        'location_id.required'     => 'Lokasi wajib diisi.',
        'location_specific.required'     => 'Lokasi Spesifik wajib diisi.',
        'description.required'     => 'Deskripsi wajib diisi.',
        'immediate_corrective_action.required'     => 'Tindakan perbaikan langsung wajib diisi.',
        'department_id.required_without' => 'Departemen wajib dipilih jika kontraktor tidak diisi.',
        'contractor_id.required_without' => 'Kontraktor wajib dipilih jika departemen tidak diisi.',
        'kondisi_tidak_aman.required_without' => 'Kondisi Tidak Aman wajib dipilih jika Tindakan Tidak Aman tidak diisi.',
        'tindakan_tidak_aman.required_without' => 'Tindakan Tidak Aman wajib dipilih jika Kondisi Tidak Aman tidak diisi.',
        'pelapor_id.required' => 'Pelapor wajib dipilih.',
        'penanggungJawab.required' => 'Penanggung jawab area wajib dipilih.',
        'tipe_bahaya.required'     => 'Tipe Bahaya wajib dipilih.',
        'sub_tipe_bahaya.required' => 'Sub Tipe Bahaya wajib dipilih.',
        'tanggal.required'         => 'Tanggal wajib dipilih.',
        'tanggal.date'             => 'Tanggal harus berupa format tanggal valid.',
        'new_doc_deskripsi.file'    => 'File deskripsi harus berupa berkas.',
        'new_doc_deskripsi.mimes'   => 'File deskripsi hanya boleh berupa JPG, JPEG, PNG, atau PDF.',
        'new_doc_deskripsi.max'     => 'Ukuran file deskripsi maksimal 2 MB.',

        'new_doc_corrective.file'   => 'File tindakan perbaikan harus berupa berkas.',
        'new_doc_corrective.mimes'  => 'File tindakan perbaikan hanya boleh berupa JPG, JPEG, PNG, atau PDF.',
        'new_doc_corrective.max'    => 'Ukuran file tindakan perbaikan maksimal 2 MB.',
    ];

    public function getTextColor($status)
    {
        $map = [
            'cancelled'   => 'error',
            'closed'      => 'success',
            'in_progress' => 'warning',
            'pending'     => 'accent',
            'submitted'   => 'info',
        ];

        return $map[$status] ?? 'neutral';
    }
    public function getRandomBadgeColor($status)
    {
        $map = [
            'cancelled'   => 'badge-error',
            'closed'      => 'badge-success',
            'in_progress' => 'badge-warning',
            'pending'     => 'badge-accent',
            'submitted'   => 'badge-info',
        ];

        return $map[$status] ?? 'badge-neutral';
    }

    public function mount(Hazard $hazard)
    {


        $this->authorize('view', $hazard);
        $this->hazard = $hazard;
        $this->hazard_id = $hazard->id;
        $this->likelihoods = Likelihood::orderByDesc('level')->get();
        $this->consequences = RiskConsequence::orderBy('level')->get();

        $this->tanggal = Carbon::createFromFormat('Y-m-d H:i:s', $this->hazard->tanggal)->format('d-m-Y H:i');
        $this->tipe_bahaya = $this->hazard->event_type_id;
        $this->sub_tipe_bahaya = $this->hazard->event_sub_type_id;
        $this->status = $this->hazard->status;
        $this->department_id = $this->hazard->department_id;
        $this->contractor_id = $this->hazard->contractor_id;
        $this->pelapor_id = $this->hazard->pelapor_id;
        $this->penanggungJawab = $this->hazard->penanggung_jawab_id;
        $this->location_id = $this->hazard->location_id;
        $this->location_specific = $this->hazard->location_specific;
        $this->description = $this->hazard->description;
        $this->dispatch('update-editor-data', name: 'description', value: $this->description);
        $this->doc_deskripsi = $this->hazard->doc_deskripsi;
        $this->immediate_corrective_action = $this->hazard->immediate_corrective_action;
        $this->dispatch('update-editor-data', name: 'immediate_corrective_action', value: $this->immediate_corrective_action);
        $this->doc_corrective = $this->hazard->doc_corrective;
        $this->keyWord = $this->hazard->key_word;
        $this->kondisi_tidak_aman = $this->hazard->kondisi_tidak_aman_id;
        $this->tindakan_tidak_aman = $this->hazard->tindakan_tidak_aman_id;
        $this->consequence_id = $this->hazard->consequence_id;
        $this->likelihood_id = $this->hazard->likelihood_id;
        $this->moderator_comment = $this->hazard->moderator_comment;
        // ✅ Load nama untuk ditampilkan di search input
        if ($this->pelapor_id) {
            // ✅ Jika pelapor_id ada → ambil nama user
            $this->searchPelapor = User::find($this->pelapor_id)?->name ?? $this->hazard->manualPelaporName ?? '';
            $this->audit_name = User::find($this->pelapor_id)?->name ?? $this->hazard->manualPelaporName;
            $this->manualPelaporName = $this->searchPelapor; // biar konsisten juga
        } else {
            // ✅ Jika pelapor_id null → pakai manualPelaporName dari DB
            $this->manualPelaporName = $this->hazard->manualPelaporName ?? '';
            $this->searchPelapor     = $this->manualPelaporName;
            $this->manualPelaporMode = true; // langsung aktifkan mode input manual
        }
        if ($this->department_id) {
            $department = Department::with('users')->find($this->department_id);
            $this->search =  $department?->department_name ?? '';
            // Ambil user dari erm_assignments berdasarkan department_id
            $this->penanggungJawabOptions = ErmAssignment::where('department_id', $this->department_id)
                ->with('user:id,name')   // pastikan relasi user() ada di model
                ->get()
                ->pluck('user')
                ->filter()
                ->toArray();
            $this->deptCont = 'department';
        }
        if ($this->contractor_id) {
            $contractor = Contractor::with('users')->find($this->contractor_id);
            $this->searchContractor = $contractor?->contractor_name ?? '';
            // Ambil user dari erm_assignments berdasarkan contractor_id
            $this->penanggungJawabOptions = ErmAssignment::where('contractor_id', $this->contractor_id)
                ->with('user:id,name')
                ->get()
                ->pluck('user')
                ->filter()
                ->toArray();
            $this->deptCont = 'company';
        }
        if ($this->location_id) {
            $this->searchLocation = Location::find($this->location_id)?->name ?? '';
        }

        if ($this->department_id) {
            $this->deptCont = 'department';
            $this->search = Department::find($this->department_id)?->department_name ?? '';
        }
        if ($this->contractor_id) {
            $this->deptCont = 'company';
            $this->searchContractor = Contractor::find($this->contractor_id)?->contractor_name ?? '';
        }
        if (!empty($this->likelihood_id) && !empty($this->consequence_id)) {
            $id_table = RiskMatrixCell::where('likelihood_id', $this->likelihood_id)->where('risk_consequence_id', $this->consequence_id)->first()->id;
            $risk_assessment_id = RiskAssessmentMatrix::where('risk_matrix_cell_id', $id_table)->first()->risk_assessment_id;
            $this->RiskAssessment = RiskAssessment::whereId($risk_assessment_id)->first();
        }

        $this->loadActionHazards();
    }
    protected function setEffectiveRole(): void
    {
        $userId = Auth::id();
        $dept   = $this->hazard->department_id;
        $cont   = $this->hazard->contractor_id;
        $comp   = null;

        // Ambil company_id dari departemen atau kontraktor (jika ada)
        if ($dept) {
            $comp = DB::table('departments')->where('id', $dept)->value('company_id');
        }

        if (!$comp && $cont) {
            $comp = DB::table('contractors')->where('id', $cont)->value('company_id');
        }

        /**
         * 🔹 Cek apakah user adalah ERM
         * Jika dept/contractor kosong → cek role global tanpa filter
         */
        $isErmQuery = DB::table('erm_assignments')->where('user_id', $userId);
        if ($dept || $cont) {
            $isErmQuery->where(function ($q) use ($dept, $cont) {
                if ($dept) {
                    $q->orWhere('department_id', $dept);
                }
                if ($cont) {
                    $q->orWhere('contractor_id', $cont);
                }
            });
        }
        $isErm = $isErmQuery->exists();

        /**
         * 🔹 Cek apakah user adalah Moderator
         *  - Moderator Global  => bisa akses semua
         *  - Moderator Lokal   => hanya akses jika dept/contractor cocok
         */
        $isModQuery = DB::table('moderator_assignments')->where('user_id', $userId);

        // Cek apakah user adalah moderator global
        $isGlobalMod = (clone $isModQuery)
            ->whereNull('department_id')
            ->whereNull('contractor_id')
            ->exists();

        // Jika bukan global, cek berdasarkan dept/contractor/company
        if (! $isGlobalMod) {
            if ($dept || $cont || $comp) {
                $isModQuery->where(function ($q) use ($dept, $cont, $comp) {
                    if ($dept) {
                        $q->orWhere('department_id', $dept);
                    }
                    if ($cont) {
                        $q->orWhere('contractor_id', $cont);
                    }
                    if ($comp) {
                        $q->orWhere('company_id', $comp);
                    }
                });
            }
        }

        $isMod = $isGlobalMod || $isModQuery->exists();
        // --- Cek role yang diizinkan berdasarkan workflow
        $currentStatus = $this->hazard->status;
        $roles = [];
        if ($isMod) $roles[] = 'moderator';
        if ($isErm) $roles[] = 'erm';

        $allowedRole = DB::table('hazard_workflows')
            ->whereIn('role', $roles)
            ->where('from_status', $currentStatus)
            ->pluck('role')
            ->first();

        // Set effectiveRole
        $this->effectiveRole = $allowedRole ?? '';
        $this->asModerator   = $this->effectiveRole === 'moderator';
        $this->asErm         = $this->effectiveRole === 'erm';
    }

    protected function loadAvailableTransitions(): void
    {
        $this->availableTransitions = HazardWorkflow::getAvailableTransitions(
            $this->hazard->status,
            $this->effectiveRole
        );
    }
    protected function loadErmList(): void
    {
        $dept = $this->hazard->department_id;
        $cont = $this->hazard->contractor_id;
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
    public function processAction()
    {
        $newStatus = $this->proceedTo;

        // --- 1. Validasi Transisi ---
        if (!HazardWorkflow::isValidTransition($this->hazard->status, $newStatus, $this->effectiveRole)) {
            session()->flash('message', 'Transisi tidak valid untuk peran dan status saat ini.');
            return;
        }

        // --- 2. Proses 'in_progress' dan Penugasan ERM ---
        $assignedErmIds = [];
        if ($newStatus === 'in_progress') {
            // ID ERM yang baru ditugaskan
            $assignIds = array_filter([$this->assignTo1, $this->assignTo2]);
            $this->hazard->assignedErms()->sync($assignIds);
            $assignedErmIds = $assignIds; // Simpan ID untuk notifikasi
        }

        // --- 3. Update dan Simpan Status Hazard ---
        $this->hazard->status = $newStatus;
        $this->hazard->save();

        // --- 4. Persiapan Data untuk Notifikasi ---

        // 4a. Tentukan Nama Lokasi/Penugasan (Department atau Contractor)
        $locationName = 'N/A';
        if ($this->hazard->department_id && $this->hazard->department) {
            $locationName = $this->hazard->department->department_name;
        } elseif ($this->hazard->contractor_id && $this->hazard->contractor) {
            $locationName = $this->hazard->contractor->name;
        }

        // 4b. Tentukan Nama Pelapor
        $reporterName = 'Tidak Diketahui';
        if ($this->hazard->pelapor_id) {
            $reporterName = $this->hazard->pelapor?->name ?? 'User Terdaftar';
        } else {
            $reporterName = $this->hazard->manualPelaporName ?? 'Anonim';
        }

        // Format Additional Info (menggunakan \n)
        $additionalInfo = "Nomor Laporan: {$this->hazard->no_referensi}\nNama Pelapor: $reporterName\nLokasi Penugasan: $locationName\nStatus: " . ucfirst(str_replace('_', ' ', $newStatus));

        // --- 5. Logika Pengiriman Notifikasi Email ---

        // A. Notifikasi ERM (Hanya jika status berubah ke 'in_progress' dan ada penugasan baru)
        if ($newStatus === 'in_progress' && !empty($assignedErmIds)) {

            foreach ($assignedErmIds as $userId) {
                defer(fn() => MailHelper::sendToUserId(
                    $userId,
                    'Notifikasi Penugasan ERM Laporan Hazard',
                    'emails.notification', // Gunakan template notifikasi yang sama
                    [
                        'subject'        => 'Anda Ditugaskan pada Laporan Hazard: ' . $this->hazard->no_referensi,
                        'title'          => 'Penugasan ERM Baru',
                        'messageText'    => "Anda baru saja ditugaskan sebagai ERM pada laporan hazard ini. Mohon segera lakukan tindakan yang diperlukan.",
                        'additionalInfo' => $additionalInfo,
                        'actionUrl'      => route('hazard-detail', $this->hazard->id)
                    ]
                ));
            }
        }
        if ($newStatus != 'in_progress') {
            // Kirim notifikasi ke moderator
            $moderatorIds = HazardWorkflow::getModeratorsForStatus($newStatus, $this->hazard);
            foreach ($moderatorIds as $moderatorId) {
                defer(fn() => MailHelper::sendToUserId(
                    $moderatorId,
                    'Notifikasi Perubahan Status Laporan Hazard',
                    'emails.notification',
                    [
                        'subject'        => 'Status Laporan Hazard Berubah: ' . $this->hazard->no_referensi,
                        'title'          => 'Perubahan Status',
                        'messageText'    => "Status laporan hazard telah diperbarui menjadi **" . ucfirst(str_replace('_', ' ', $newStatus)) . "**.",
                        'additionalInfo' => $additionalInfo,
                        'actionUrl'      => route('hazard-detail', $this->hazard->id)
                    ]
                ));
            }
        }

        // B. Notifikasi ke Penanggung Jawab (Opsional: Jika Anda ingin memberitahu PJ tentang perubahan status)
        if ($this->hazard->penanggung_jawab_id && $newStatus !== 'submitted') {
            defer(fn() => MailHelper::sendToUserId(
                $this->hazard->penanggung_jawab_id,
                'Perubahan Status Laporan Hazard',
                'emails.notification',
                [
                    'subject'        => 'Status Laporan Hazard Berubah: ' . $this->hazard->no_referensi,
                    'title'          => 'Perubahan Status',
                    'messageText'    => "Status laporan hazard yang Anda tangani telah diperbarui menjadi **" . ucfirst(str_replace('_', ' ', $newStatus)) . "**.",
                    'additionalInfo' => $additionalInfo,
                    'actionUrl'      => route('hazard-detail', $this->hazard->id)
                ]
            ));
        }

        // --- 6. Update UI dan Feedback ke User ---
        $this->loadAvailableTransitions();
        $isDisabled = in_array($newStatus, ['cancelled', 'closed']);
        $this->dispatch('hazardStatusChanged', ['isDisabled' => $isDisabled]);

        $this->dispatch(
            'alert',
            [
                'text' => "Status Berhasil di Perbaharui!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => false,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]
        );
        $this->reset('proceedTo');
    }
    public function getHasUnreadProperty()
    {
        $lastChat = $this->hazard->chats->last();

        return $lastChat && $lastChat->user_id !== Auth::id();
    }
    public function sendMessage()
    {
        $this->validate(['newMessage' => 'required|string|max:500']);

        $isModerator = $this->hazard->isModerator();

        // Ambil semua chat untuk pengecekan moderator yang pernah chat
        $allChats = $this->hazard->chats()->get();

        $hasModeratorChatted = $allChats->contains(function ($chat) {
            return $this->hazard->isModerator($chat->user_id);
        });

        // Validasi: Moderator harus mulai duluan
        if (!$isModerator && !$hasModeratorChatted) {
            $this->addError('newMessage', 'Anda tidak dapat mengirim pesan sebelum moderator memulai.');
            return;
        }

        // 1. Simpan Pesan Baru
        HazardChat::create([
            'hazard_report_id' => $this->hazard->id,
            'user_id' => auth()->id(),
            'message' => $this->newMessage,
        ]);

        $currentUserId = auth()->id();
        $noRef = $this->hazard->no_referensi;

        // 2. Tentukan Siapa yang Menerima Email
        $recipientIds = collect();

        if ($isModerator) {
            // 1. Ambil semua ID user dari chat yang BUKAN moderator (Pihak ERM)
            $ermChatterIds = $allChats->filter(function ($chat) {
                return !$this->hazard->isModerator($chat->user_id);
            })->pluck('user_id')->unique();

            // 2. Cek apakah sudah ada ERM yang pernah chat
            if ($ermChatterIds->isNotEmpty()) {
                // JIKA SUDAH ADA: Kirim ke semua ERM yang pernah ikut chat saja
                $recipientIds = $ermChatterIds;
            } else {
                // JIKA BELUM ADA: Kirim ke semua ERM yang ditugaskan di Dept/Kontraktor ini
                $recipientIds = ErmAssignment::query()
                    ->when($this->hazard->department_id, function ($q) {
                        return $q->where('department_id', $this->hazard->department_id);
                    })
                    ->when($this->hazard->contractor_id, function ($q) {
                        return $q->orWhere('contractor_id', $this->hazard->contractor_id);
                    })
                    ->pluck('user_id')
                    ->unique();
            }
        } else {
            // SKENARIO B: ERM yang chat -> Kirim ke Moderator (Tetap sama)
            $recipientIds = $allChats->filter(function ($chat) {
                return $this->hazard->isModerator($chat->user_id);
            })->pluck('user_id')->unique();
        }
        // 3. Kirim Email ke Recipient (Kecuali diri sendiri)
        foreach ($recipientIds->toArray() as $userId) {
            // Lewati jika ID penerima adalah orang yang sedang mengirim chat
            if ($userId == $currentUserId) continue;

            try {
                $user = User::find($userId);
                defer(fn() => MailHelper::sendToUserId(
                    $userId,
                    'Notifikasi Pesan Baru di Chat Laporan Hazard',
                    'emails.notification',
                    [
                        'subject'        => 'Pesan Baru di Chat Laporan Hazard: ' . $noRef,
                        'title'          => 'Ada Pesan Baru untuk Anda',
                        'messageText'    => "Seseorang telah mengirimkan pesan baru pada diskusi laporan Hazard dengan nomor referensi **{$noRef}** cek di **Chat Laporan Hazard**.",
                        'additionalInfo' => "Pengirim: " . auth()->user()->name . "\n" .
                            "Isi Pesan: " . $this->newMessage . "\n" . // Menambahkan pesan chat di sini
                            "Waktu: " . now()->format('d M Y H:i') . "\n" .
                            "Status Laporan: " . ucfirst(str_replace('_', ' ', $this->hazard->status)),
                        'actionUrl'      => route('hazard-detail', $this->hazard->id),
                        'actionText'     => 'Lihat Pesan di Aplikasi'
                    ]
                ));
            } catch (\Exception $e) {
                // Catat error ke log jika pengiriman ke satu user ini gagal, tapi biarkan loop lanjut ke user berikutnya
                $this->dispatch(
                    'alert',
                    [
                        'text' => "Gagal mengirim email chat ke User ID {$userId}: " . $e->getMessage(),
                        'duration' => 5000,
                        'destination' => '/contact',
                        'newWindow' => true,
                        'close' => true,
                        'backgroundColor' => "linear-gradient(to right, #ff3333, #ff6666)",
                    ]
                );
            }
        }


        $this->reset('newMessage');
        $this->hazard->refresh();
        // Tambahkan ini agar JS tahu harus scroll ke bawah
        $this->dispatch('scroll-bottom');
    }
    public function markAsRead()
    {
        HazardChat::where('hazard_report_id', $this->hazard_id)
            ->where('user_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->hazard->refresh();
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

        // Ambil user dari erm_assignments berdasarkan contractor_id
        $this->penanggungJawabOptions = ErmAssignment::where('department_id', $id)
            ->with('user:id,name')
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
            $this->showContractorDropdown = true;
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
        if (strlen($this->searchLocation) > 1) {
            $this->locations = Location::where('name', 'like', '%' . $this->searchLocation . '%')
                ->orderBy('name')
                ->limit(10)
                ->get();
            $this->showLocationDropdown = true;
        } else {
            $this->locations = [];
            $this->showLocationDropdown = false;
        }
    }
    public function selectLocation($id, $name)
    {
        $this->location_id = $id;
        $this->searchLocation = $name;
        $this->showLocationDropdown = false;
        $this->validateOnly('location_id');
    }
    public function updatedSearchPelapor()
    {
        $this->reset('manualPelaporName');
        $this->manualPelaporMode = false;
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
        $this->validateOnly('pelapor_id');
    }
    public function enableManualPelapor()
    {
        $this->manualPelaporMode = true;
        $this->manualPelaporName = $this->searchPelapor; // isi default sama dengan isi search
    }
    public function updatedManualPelaporName($value)
    {
        $this->pelapor_id = null;
    }
    public function addPelaporManual()
    {
        $this->searchPelapor = $this->manualPelaporName;
        $this->showPelaporDropdown = false;
    }
    public function updatedSearchActResponsibility()
    {
        $this->reset('manualActPelaporName');
        $this->manualActPelaporMode = false;
        if (strlen($this->searchActResponsibility) > 1) {
            $this->pelaporsAct = User::where('name', 'like', '%' . $this->searchActResponsibility . '%')
                ->orderBy('name')
                ->limit(10)
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
    public function getIsFormValidProperty()
    {
        // Kalau user pilih department_id atau contractor_id salah satu boleh
        $validCompanyDept = $this->department_id || $this->contractor_id;
        return $validCompanyDept
            && !empty($this->location)
            && !empty($this->description)
            && !empty($this->severity);
    }

    public function submit()
    {
        $this->validate();
        $docDeskripsiPath = null;
        $docCorrectivePath = null;
        // Convert tanggal ke format Y-m-d H:i:s
        $tanggal = Carbon::createFromFormat('d-m-Y H:i', $this->tanggal)->format('Y-m-d H:i');
        // Update file sebelum perbaikan
        if ($this->new_doc_deskripsi) {
            // Hapus file lama (opsional)
            if ($this->doc_deskripsi && Storage::disk('public')->exists($this->doc_deskripsi)) {
                Storage::disk('public')->delete($this->doc_deskripsi);
            }

            // Simpan file baru
            $docDeskripsiPath = FileHelper::compressAndStore($this->new_doc_deskripsi, 'sebelum_perbaikan');
            $this->doc_deskripsi = $docDeskripsiPath;
        }

        // Update file sesudah perbaikan
        if ($this->new_doc_corrective) {
            if ($this->doc_corrective && Storage::disk('public')->exists($this->doc_corrective)) {
                Storage::disk('public')->delete($this->doc_corrective);
            }

            $docCorrectivePath = FileHelper::compressAndStore($this->new_doc_corrective, 'sesudah_perbaikan');
            $this->doc_corrective = $docCorrectivePath;
        }
        // Hitung risk level (opsional: ambil dari RiskMatrixCell atau kalkulasi manual)
        $riskLevel = null;
        if ($this->consequence_id && $this->likelihood_id) {
            $riskLevel = \App\Models\RiskMatrixCell::where('likelihood_id', $this->likelihood_id)
                ->where('risk_consequence_id', $this->consequence_id)
                ->value('severity');
        }
        $updateData = [
            'event_type_id'          => $this->tipe_bahaya,
            'event_sub_type_id'      => $this->sub_tipe_bahaya,
            'department_id'          => $this->department_id,
            'contractor_id'          => $this->contractor_id,
            'pelapor_id'             => $this->pelapor_id,
            'penanggung_jawab_id'    => $this->penanggungJawab,
            'location_id'            => $this->location_id,
            'location_specific'      => $this->location_specific,
            'moderator_comment'      => $this->moderator_comment,
            'tanggal'                => $tanggal,
            'description'            => $this->description,
            'immediate_corrective_action' => $this->immediate_corrective_action,
            'key_word'               => $this->keyWord,
            'kondisi_tidak_aman_id'  => $this->kondisi_tidak_aman,
            'tindakan_tidak_aman_id' => $this->tindakan_tidak_aman,
            'consequence_id'         => $this->consequence_id,
            'likelihood_id'          => $this->likelihood_id,
            'risk_level'             => $riskLevel,
            'manualPelaporName' => $this->pelapor_id ? User::find($this->pelapor_id)?->name : $this->manualPelaporName,
        ];

        // Hanya update kalau ada file baru
        if ($docDeskripsiPath) {
            $updateData['doc_deskripsi'] = $docDeskripsiPath;
        }

        if ($docCorrectivePath) {
            $updateData['doc_corrective'] = $docCorrectivePath;
        }

        $hazard = Hazard::findOrFail($this->hazard->id);
        $hazard->update($updateData);

        // --- Tentukan Nama Lokasi/Penugasan yang Akan Ditampilkan di Email ---
        $locationName = 'N/A';
        if ($hazard->department_id && $hazard->department) {
            // Jika Department ada, gunakan namanya
            $locationName = $hazard->department->department_name;
        } elseif ($hazard->contractor_id && $hazard->contractor) {
            // Jika Department NULL/kosong, dan Contractor ada, gunakan namanya
            // Asumsi: Nama kolom di model Department adalah 'department_name'
            // dan nama kolom di model Contractor adalah 'name' (sesuaikan jika berbeda)
            $locationName = $hazard->contractor->contractor_name;
        }

        // [START] Logika Baru Penentuan Nama Pelapor
        $reporterName = 'Tidak Diketahui';
        if ($hazard->pelapor_id) {
            // Jika ada ID pelapor, ambil dari relasi User
            // Asumsi relasi User di model Hazard bernama 'pelapor'.
            // Menggunakan optional chaining (?->) untuk keamanan jika relasi belum dimuat.
            $reporterName = $hazard->pelapor?->name ?? 'User Terdaftar';
        } else {
            // Jika tidak ada ID pelapor, ambil dari input manual
            $reporterName = $hazard->manualPelaporName ?? 'Anonim';
        }
        // [END] Logika Baru Penentuan Nama Pelapor
        // 3. Notifikasi
        // Dapatkan Penanggung Jawab dari relasi
        $penanggungJawab = $hazard->penanggung_jawab_id;
        if ($penanggungJawab) {
            defer(fn() => MailHelper::sendToUserId(
                $penanggungJawab,
                'Notifikasi Laporan Hazard',
                'emails.notification',
                [
                    'subject'       => 'Update Laporan Hazard ',
                    'title'         => 'Notifikasi Laporan Hazard',
                    'messageText'   => "Telah diupdate laporan hazard .\nSilakan lakukan  pemeriksaan.",
                    'additionalInfo' => "Nomor Laporan: $hazard->no_referensi",
                    'actionUrl'     => route('hazard-detail', $hazard->id)
                ]
            ));
        }
        if ($this->moderator_comment) {
            // 1. Kumpulkan semua ID yang berpotensi di-assign
            $rawAssignIds = [
                $this->assignTo1,
                $this->assignTo2,
                $penanggungJawab
            ];

            // 2. Filter nilai kosong (null) dan hilangkan ID duplikat
            // array_unique mencegah pengiriman email 2x ke orang yang sama
            $uniqueAssignIds = array_unique(array_filter($rawAssignIds));

            // 3. Sync ke database
            $this->hazard->assignedErms()->sync($uniqueAssignIds);

            // 4. Looping untuk kirim email ke semua pihak yang terlibat
            foreach ($uniqueAssignIds as $userId) {
                defer(fn() => MailHelper::sendToUserId(
                    $userId,
                    'Komentar Moderator pada Laporan Hazard',
                    'emails.notification',
                    [
                        'subject'        => 'Komentar Moderator: ' . $hazard->no_referensi,
                        'title'          => 'Komentar Baru dari Moderator',
                        'messageText'    => "Moderator telah menambahkan komentar pada laporan hazard ini:\n\n\"{$this->moderator_comment}\"\n\nSilakan tinjau komentar tersebut dan lakukan tindakan yang diperlukan.",
                        'additionalInfo' => "Nomor Laporan: $hazard->no_referensi",
                        'actionUrl'      => route('hazard-detail', $hazard->id)
                    ]
                ));
            }
        }

        // [START] Logika Baru: Notifikasi ke Semua Moderator
        // Dapatkan semua ID pengguna moderator yang relevan
        // Dapatkan semua ID pengguna moderator yang relevan
        $moderatorIds = \App\Models\ModeratorAssignment::where('event_type_id', $hazard->event_type_id)
            ->where(function ($query) use ($hazard) {
                // Moderator ditugaskan untuk Event Type ini,
                // DAN penugasan tersebut harus berlaku (cocok dengan laporan)

                // Kriteria 1: Penugasan bersifat umum (department_id dan contractor_id di assignment adalah NULL)
                $query->whereNull('department_id')
                    ->whereNull('contractor_id');

                // Kriteria 2: Penugasan spesifik untuk Department
                if ($hazard->department_id) {
                    $query->orWhere('department_id', $hazard->department_id);
                }

                // Kriteria 3: Penugasan spesifik untuk Contractor
                if ($hazard->contractor_id) {
                    $query->orWhere('contractor_id', $hazard->contractor_id);
                }
            })
            ->distinct('user_id')
            ->pluck('user_id');
        // Kirim email ke setiap moderator
        foreach ($moderatorIds as $moderatorId) {
            defer(fn() => MailHelper::sendToUserId(
                $moderatorId,
                'Notifikasi Laporan Hazard',
                'emails.notification',
                [
                    'subject'       => 'Update Laporan Hazard ',
                    'title'         => 'Notifikasi Laporan Hazard',
                    'messageText'   => "Telah diupdate laporan hazard .\nSilakan lakukan  pemeriksaan.",
                    'additionalInfo' => "Nomor Laporan: $hazard->no_referensi",
                    'actionUrl'     => route('hazard-detail', $hazard->id)
                ]
            ));
        }
        // [END] Logika Baru: Notifikasi ke Semua Moderator
        $this->dispatch(
            'alert',
            [
                'text' => "Laporan berhasil diupdate!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]
        );
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


    public function addActionHazard()
    {
        $this->dispatch('validate-action_description');
        $this->validate(
            [
                'action_description'       => 'required|string',
                'action_responsible_id'    => 'nullable|exists:users,id',
                // Due date harus sebelum atau sama dengan actual close date (jika close date ada)
                'action_due_date'       => [
                    'nullable',
                    'date_format:d-m-Y',
                    'before_or_equal:action_actual_close_date'
                ],

                // Actual close date harus sesudah atau sama dengan due date
                'action_actual_close_date' => [
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

                'action_actual_close_date.date_format'    => 'Format tanggal harus dd-mm-YYYY.',
                'action_actual_close_date.after_or_equal' => 'Tanggal penyelesaian tidak boleh lebih kecil dari tanggal batas waktu.',

                'action_responsible_id.required' => 'Penanggung jawab wajib dipilih.',
            ]

        );


        ActionHazard::create([
            'hazard_id'        => $this->hazard->id,
            'orginal_date'     => $this->action_due_date, // atau bisa diambil dari form jika ada
            'description'      => $this->action_description,
            'due_date'         => Carbon::parse($this->action_due_date),
            'actual_close_date' => $this->action_actual_close_date
                ? Carbon::parse($this->action_actual_close_date)
                : null,
            'responsible_id'   => $this->action_responsible_id,
        ]);
        $this->dispatch(
            'alert',
            [
                'text' => "Action Hazard berhasil ditambahkan!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #42a5f5, #478ed1);",
            ]
        );
        // Refresh list setelah simpan
        $this->loadActionHazards();

        // Reset form
        $this->reset([
            'action_description',
            'action_due_date',
            'action_actual_close_date',
            'action_responsible_id',
            'searchActResponsibility',
        ]);
    }
    public function removeAction($id)
    {
        // Jika datanya hanya di array property
        $this->actionHazards = collect($this->actionHazards)
            ->reject(fn($act) => $act['id'] == $id)
            ->values()
            ->toArray();
        // Jika mau hapus di database juga:
        $action = ActionHazard::find($id);
        if ($action) {
            $action->delete(); // event deleted + activity log terpicu
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
    public function loadEditAction($id)
    {
        $action = ActionHazard::findOrFail($id);
        $this->edit_action_id               = $action->id;
        $this->edit_action_description      = $action->description;
        $this->edit_action_due_date         = optional($action->due_date)->format('d-m-Y');
        $this->edit_action_actual_close_date = optional($action->actual_close_date)->format('d-m-Y');
        $this->edit_action_responsible_id   = $action->responsible_id;
        $this->searchActResponsibilityEdit   = optional(User::find($action->responsible_id))->name;
        $this->dispatch('update-editor-data', name: 'edit_action_description', value: $this->edit_action_description);
        // kirim event ke Alpine supaya modal dibuka setelah data siap
        $this->dispatch('open-edit-action');
    }

    public function updatedSearchActResponsibilityEdit()
    {
        $this->reset('manualActPelaporName');
        $this->manualActPelaporModeEdit = false;
        if (strlen($this->searchActResponsibilityEdit) > 1) {
            $this->pelaporsActEdit = User::where('name', 'like', '%' . $this->searchActResponsibilityEdit . '%')
                ->orderBy('name')
                ->limit(10)
                ->get();
            $this->showActPelaporDropdownEdit = true;
        } else {
            $this->pelaporsActEdit = [];
            $this->showActPelaporDropdownEdit = false;
        }
    }
    public function selectActPelaporEdit($id, $name)
    {
        $this->edit_action_responsible_id = $id;
        $this->searchActResponsibilityEdit = $name;
        $this->showActPelaporDropdownEdit = false;
        $this->manualActPelaporModeEdit = false;
        $this->validateOnly('edit_action_responsible_id');
    }
    public function addActPelaporManualEdit()
    {
        $this->searchActResponsibilityEdit = $this->manualActPelaporNameEdit;
        $this->showActPelaporDropdownEdit = false;
    }
    public function enableManualActPelaporEdit()
    {
        $this->manualActPelaporModeEdit = true;
        $this->manualActPelaporNameEdit = $this->searchActResponsibilityEdit; // isi default sama dengan isi search
    }

    public function updateAction()
    {
        $this->validate(
            [
                'edit_action_description'       => 'required|string',
                'edit_action_responsible_id'    => 'nullable|integer',
                // Validasi file baru (opsional, maks 5MB, sesuaikan tipe file jika perlu)
                'edit_action_final_doc'         => 'nullable|file|max:5120',

                // Due date harus sebelum atau sama dengan actual close date (jika close date ada)
                'edit_action_due_date'          => [
                    'required',
                    'date_format:d-m-Y',
                    'before_or_equal:edit_action_actual_close_date'
                ],

                // Actual close date harus sesudah atau sama dengan due date
                'edit_action_actual_close_date' => [
                    'nullable',
                    'date_format:d-m-Y',
                    'after_or_equal:edit_action_due_date'
                ],
            ],
            [
                'edit_action_description.required'  => 'Deskripsi tindakan wajib diisi.',
                'edit_action_due_date.required'     => 'Tanggal batas waktu wajib diisi.',
                'edit_action_due_date.date_format'  => 'Format tanggal harus dd-mm-YYYY.',
                'edit_action_due_date.before_or_equal' => 'Tanggal batas waktu tidak boleh melampaui tanggal penyelesaian.',

                'edit_action_actual_close_date.date_format'    => 'Format tanggal harus dd-mm-YYYY.',
                'edit_action_actual_close_date.after_or_equal' => 'Tanggal penyelesaian tidak boleh lebih kecil dari tanggal batas waktu.',

                'edit_action_responsible_id.required' => 'Penanggung jawab wajib dipilih.',
                'edit_action_final_doc.max'           => 'Ukuran dokumen maksimal adalah 5MB.',
            ]
        );

        $action = ActionHazard::findOrFail($this->edit_action_id);

        // 1. Siapkan data standar yang akan di-update
        $updateData = [
            'description'       => $this->edit_action_description,
            'due_date'          => Carbon::createFromFormat('d-m-Y', $this->edit_action_due_date),
            'actual_close_date' => $this->edit_action_actual_close_date ? Carbon::createFromFormat('d-m-Y', $this->edit_action_actual_close_date) : null,
            'responsible_id'    => $this->edit_action_responsible_id,
        ];

        // 2. Logika Tambahan: Jika user mengunggah file final_doc baru
        if ($this->edit_action_final_doc) {
            // Simpan file baru lewat FileHelper
            $newDocPath = FileHelper::compressAndStore($this->edit_action_final_doc, 'action_hazard_docs');

            // Ambil array data lama langsung dari database (default array kosong jika masih null)
            $existingDocs = $action->final_doc ?? [];

            // Gabungkan file baru ke tumpukan array dokumen lama
            $existingDocs[] = $newDocPath;

            // Masukkan array yang sudah diperbarui ke data update
            $updateData['final_doc'] = $existingDocs;
        }

        // 3. Eksekusi update data ke database
        $action->update($updateData);

        // 4. Reset & Sinkronisasi properti input file setelah berhasil disimpan
        $this->edit_action_final_doc = null;
        $this->existing_final_docs = $action->final_doc ?? [];

        $this->dispatch('close-modal', id: 'editActionModal');

        // Refresh list
        $this->dispatch(
            'alert',
            [
                'text' => "Action Hazard berhasil diupdate!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #42a5f5, #478ed1);",
            ]
        );

        $this->loadActionHazards();
    }
    public function loadActionHazards()
    {
        $this->actionHazards = ActionHazard::with('responsible')->where('hazard_id', $this->hazard->id)->orderByDesc('created_at')->get()->toArray();
    }

    public function deleteHazard(Hazard $hazard)
    {
        $moderatorIds = ModeratorAssignment::where('event_type_id', $hazard->event_type_id)
            ->where(function ($query) use ($hazard) {
                // Moderator ditugaskan untuk Event Type ini,
                // DAN penugasan tersebut harus berlaku (cocok dengan laporan)

                // Kriteria 1: Penugasan bersifat umum (department_id dan contractor_id di assignment adalah NULL)
                $query->whereNull('department_id')
                    ->whereNull('contractor_id');

                // Kriteria 2: Penugasan spesifik untuk Department
                if ($hazard->department_id) {
                    $query->orWhere('department_id', $hazard->department_id);
                }

                // Kriteria 3: Penugasan spesifik untuk Contractor
                if ($hazard->contractor_id) {
                    $query->orWhere('contractor_id', $hazard->contractor_id);
                }
            })
            ->distinct('user_id')
            ->pluck('user_id');
        $clearDeskription  = strip_tags($hazard->description);
        // Kirim email ke setiap moderator
        foreach ($moderatorIds as $moderatorId) {
            defer(fn() => MailHelper::sendToUserId(
                $moderatorId,
                'Penghapusan Laporan Hazard',
                'emails.notification',
                [
                    'subject'        => 'Laporan Hazard Dihapus',
                    'title'          => 'Notifikasi Penghapusan Laporan',
                    // Menambahkan nama user yang sedang login (penghapus)
                    'messageText'    => "Laporan hazard dengan nomor referensi di bawah ini telah dihapus dari sistem oleh: " . auth()->user()->name,
                    'additionalInfo' => "Nomor Laporan: $hazard->no_referensi\nDeskripsi : $clearDeskription",
                    'actionUrl'      => null // Set null agar tombol di email tidak muncul/tidak bisa diklik
                ]
            ));
        }
        $penanggungJawab = $hazard->penanggung_jawab_id;
        if ($penanggungJawab) {
            defer(fn() => MailHelper::sendToUserId(
                $penanggungJawab,
                'Penghapusan Laporan Hazard',
                'emails.notification',
                [
                    'subject'        => 'Laporan Hazard Dihapus',
                    'title'          => 'Notifikasi Penghapusan Laporan',
                    // Menambahkan nama user yang sedang login (penghapus)
                    'messageText'    => "Laporan hazard dengan nomor referensi di bawah ini telah dihapus dari sistem oleh: " . auth()->user()->name,
                    'additionalInfo' => "Nomor Laporan: $hazard->no_referensi\nDeskripsi : $clearDeskription",
                    'actionUrl'      => null // Set null agar tombol di email tidak muncul/tidak bisa diklik
                ]
            ));
        }
        $hazard->delete();

        // Setelah model dihapus (dan event 'deleting' telah dijalankan),
        // Anda bisa memberikan feedback kepada pengguna atau redirect.
        $this->dispatch(
            'alert',
            [
                'text' => "Laporan hazard berhasil dihapus!",
                'duration' => 5000,
                'destination' => route('hazard'),
                'newWindow' => false,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #ff3333, #ff6666)",
            ]
        );

        return redirect()->route('hazard');
    }
    public function render()
    {
        $this->setEffectiveRole();
        $this->loadAvailableTransitions();
        $this->loadErmList();
        return view('livewire.hazard.hazard-detail', [
            'report' => Hazard::with('activities.causer')->findOrFail($this->hazard_id),
            'Department'   => Department::all(),
            'likelihoodss' => Likelihood::orderByDesc('level')->get(),
            'consequencess' => RiskConsequence::orderBy('level')->get(),
            'Contractors'  => Contractor::all(),
            'ktas' => UnsafeCondition::latest()->get(),
            'ttas' => UnsafeAct::latest()->get(),
            'eventTypes' => EventType::where('event_type_name', 'like', '%' . 'hazard' . '%')->get(),
            'subTypes' => EventSubType::where('event_type_id', $this->tipe_bahaya)->get()
        ]);
    }
}
