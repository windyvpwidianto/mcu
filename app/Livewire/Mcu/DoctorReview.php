<?php

namespace App\Livewire\Mcu;

use App\Models\McuResult;
use App\Models\DiseaseCategory;
use App\Models\McuMasterData;
use App\Notifications\McuResultNotification;
use App\Services\McuFitLetterService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class DoctorReview extends Component
{
    use WithFileUploads;

    // Tab aktif: 'pending_doctor', 'need_specialist', 'pending_specialist_review'
    public $activeTab = 'pending_doctor';

    // --- Properti Modal Review Awal ---
    public $selectedResultId;
    public $fit_status;
    public $doctor_notes;
    public $specialist_type;
    public $follow_up_date;
    public $selectedDiseaseCategories = [];
    public $showReviewModal = false;
    public $new_disease_name;

    // --- Properti Modal Upload Dokumen Spesialis ---
    public $showSpecialistModal = false;
    public $specialistResultId;
    public $upload_specialist_document;
    public $upload_specialist_consult_date;
    public $upload_specialist_notes;

    // --- Properti Modal Re-Review / Evaluasi Ulang ---
    public $showReReviewModal = false;
    public $reReviewResultId;
    public $re_fit_status = 'fit_to_work';
    public $re_review_notes;

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function openReviewModal($id)
    {
        $this->resetValidation();
        $this->reset([
            'fit_status', 'doctor_notes', 'specialist_type', 
            'follow_up_date', 'selectedDiseaseCategories', 'new_disease_name'
        ]);

        $this->selectedResultId = $id;

        $result = McuResult::with('diseaseCategories')->find($id);
        if ($result) {
            $this->selectedDiseaseCategories = $result->diseaseCategories->pluck('id')->toArray();
            $this->fit_status = $result->status;
            $this->doctor_notes = $result->doctor_notes;
            $this->specialist_type = $result->specialist_type;
            $this->follow_up_date = $result->follow_up_date?->format('Y-m-d') ?? date('Y-m-d', strtotime('+14 days'));
        }

        $this->showReviewModal = true;
    }

    // --- Input inline penyakit baru ---
    public function saveNewDisease()
    {
        $this->validate([
            'new_disease_name' => 'required|string|min:3|unique:disease_categories,name',
        ], [
            'new_disease_name.required' => 'Nama wajib diisi.',
            'new_disease_name.unique'   => 'Penyakit sudah ada.',
            'new_disease_name.min'      => 'Min. 3 huruf.',
        ]);

        $newCategory = DiseaseCategory::create([
            'name' => trim($this->new_disease_name)
        ]);

        $this->selectedDiseaseCategories = array_diff($this->selectedDiseaseCategories, ['tambah_penyakit']);
        $this->selectedDiseaseCategories[] = (string) $newCategory->id;
        $this->new_disease_name = '';
        $this->resetValidation('new_disease_name');
    }

    // --- Simpan Review Awal Dokter ---
    public function saveReview()
    {
        $rules = [
            'fit_status'   => 'required|in:fit_to_work,fit_with_notes,temporary_unfit,unfit',
            'doctor_notes' => 'required_unless:fit_status,fit_to_work|string|nullable',
        ];

        if ($this->fit_status === 'temporary_unfit') {
            $rules['specialist_type'] = 'required|string|min:3';
            $rules['follow_up_date']  = 'required|date';
        }

        $this->validate($rules, [
            'specialist_type.required' => 'Dokter spesialis rujukan wajib diisi untuk status Temporary Unfit.',
            'follow_up_date.required'  => 'Target tanggal pemeriksaan rujukan wajib diisi.',
        ]);

        if (!$this->selectedResultId) {
            session()->flash('error', 'Data MCU tidak ditemukan.');
            return;
        }

        $result = McuResult::with(['record.employee', 'record.deptHead'])->find($this->selectedResultId);
        if (!$result) return;

        $isTemporaryUnfit = ($this->fit_status === 'temporary_unfit');

        $result->update([
            'status'              => $this->fit_status,
            'workflow_status'     => $isTemporaryUnfit ? 'need_specialist' : 'reviewed',
            'specialist_type'     => $isTemporaryUnfit ? $this->specialist_type : null,
            'follow_up_date'      => $isTemporaryUnfit ? $this->follow_up_date : null,
            'doctor_site_consult' => null,
            'doctor_notes'        => $this->doctor_notes,
            'reviewed_by'         => auth()->id(),
            'reviewed_at'         => now(),
        ]);

        // Sync kategori penyakit
        $validIds = array_filter($this->selectedDiseaseCategories, function ($id) {
            return $id !== 'tambah_penyakit' && is_numeric($id);
        });
        $result->diseaseCategories()->sync($validIds);

        // Jika FIT TO WORK / FIT WITH NOTES langsung tuntas di review awal
        if (!$isTemporaryUnfit) {
            // Update riwayat & target jadwal MCU tahun depan (+1 Tahun)
            if ($result->mcu_master_data_id) {
                $master = McuMasterData::find($result->mcu_master_data_id);
                if ($master) {
                    $master->histories()->create([
                        'historical_date' => $master->mcu_date ?? now(),
                        'notes'           => 'Hasil MCU (' . str_replace('_', ' ', $this->fit_status) . ') telah direview oleh dokter.'
                    ]);
                    $newTargetDate = \Carbon\Carbon::parse($master->mcu_date ?? now())->addYear();
                    $master->update([
                        'mcu_date'            => $newTargetDate,
                        'notification_status' => 'pending'
                    ]);
                }
            }

            // Generate Sertifikat
            if (in_array($this->fit_status, ['fit_to_work', 'fit_with_notes'])) {
                try {
                    $fullCertNumber = McuFitLetterService::generateCertificateNumber($result);
                    $result->update([
                        'certificate_number' => $fullCertNumber,
                        'letter_status'      => 'draft',
                        'letter_updated_by'  => auth()->id(),
                        'letter_updated_at'  => now(),
                    ]);
                    $draftHtml = McuFitLetterService::generateDefaultHtml($result);
                    $result->update(['letter_content' => $draftHtml]);
                    McuFitLetterService::savePdfToStorage($result);
                } catch (\Exception $e) {
                    Log::error('Gagal generate sertifikat MCU: ' . $e->getMessage());
                }
            }
        }

        // Kirim Notifikasi ke Karyawan & Dept Head
        $this->sendNotification($result);

        activity('mcu')
            ->performedOn($result)
            ->causedBy(auth()->user())
            ->withProperties([
                'fit_status'      => $this->fit_status,
                'specialist_type' => $this->specialist_type,
                'doctor_notes'    => $this->doctor_notes,
            ])
            ->log($isTemporaryUnfit ? 'Dokter menetapkan Temporary Unfit & Rujukan Spesialis' : 'Dokter melakukan review hasil MCU');

        $this->showReviewModal = false;
        $this->reset(['fit_status', 'doctor_notes', 'specialist_type', 'follow_up_date', 'selectedDiseaseCategories', 'selectedResultId']);

        if ($isTemporaryUnfit) {
            session()->flash('message', 'Hasil MCU berstatus TEMPORARY UNFIT telah disimpan. Karyawan diarahkan ke Dokter Spesialis dan notifikasi rujukan telah terkirim.');
            $this->activeTab = 'need_specialist';
        } else {
            session()->flash('message', 'Review MCU berhasil disimpan dan notifikasi hasil telah terkirim.');
        }
    }

    // --- Modal Upload Dokumen Dokter Spesialis ---
    public function openSpecialistModal($id)
    {
        $this->resetValidation();
        $this->reset(['upload_specialist_document', 'upload_specialist_consult_date', 'upload_specialist_notes']);

        $this->specialistResultId = $id;
        $result = McuResult::find($id);
        if ($result) {
            $this->upload_specialist_consult_date = $result->specialist_consult_date?->format('Y-m-d') ?? date('Y-m-d');
            $this->upload_specialist_notes = $result->specialist_notes;
        }

        $this->showSpecialistModal = true;
    }

    public function saveSpecialistDocument()
    {
        $this->validate([
            'upload_specialist_document'     => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'upload_specialist_consult_date' => 'required|date',
            'upload_specialist_notes'        => 'nullable|string',
        ], [
            'upload_specialist_document.required' => 'File surat/dokumen hasil spesialis wajib diunggah.',
            'upload_specialist_document.mimes'    => 'Format file harus PDF, JPG, JPEG, atau PNG.',
            'upload_specialist_consult_date.required' => 'Tanggal konsultasi spesialis wajib diisi.',
        ]);

        $result = McuResult::find($this->specialistResultId);
        if (!$result) return;

        // Simpan dokumen ke local disk
        $path = $this->upload_specialist_document->store('mcu_specialist_docs', 'local');

        $result->update([
            'specialist_document'     => $path,
            'specialist_consult_date' => $this->upload_specialist_consult_date,
            'specialist_notes'        => $this->upload_specialist_notes,
            'workflow_status'         => 'pending_specialist_review',
        ]);

        activity('mcu')
            ->performedOn($result)
            ->causedBy(auth()->user())
            ->log('Dokumen hasil pemeriksaan dokter spesialis telah diunggah');

        $this->showSpecialistModal = false;
        $this->reset(['upload_specialist_document', 'upload_specialist_consult_date', 'upload_specialist_notes', 'specialistResultId']);

        session()->flash('message', 'Dokumen hasil spesialis berhasil diunggah! Data karyawan berpindah ke antrean "Siap Re-evaluasi Dokter".');
        $this->activeTab = 'pending_specialist_review';
    }

    // --- Modal Re-Review / Evaluasi Ulang Dokter ---
    public function openReReviewModal($id)
    {
        $this->resetValidation();
        $this->reset(['re_fit_status', 're_review_notes']);

        $this->reReviewResultId = $id;
        $this->re_fit_status = 'fit_to_work';
        $this->showReReviewModal = true;
    }

    public function saveReReview()
    {
        $this->validate([
            're_fit_status'   => 'required|in:fit_to_work,fit_with_notes,temporary_unfit,unfit',
            're_review_notes' => 'required|string|min:5',
        ], [
            're_fit_status.required'   => 'Status kebugaran hasil re-evaluasi wajib dipilih.',
            're_review_notes.required' => 'Catatan evaluasi akhir dokter wajib diisi.',
        ]);

        $result = McuResult::with(['record.employee', 'record.deptHead'])->find($this->reReviewResultId);
        if (!$result) return;

        $isStillTemporary = ($this->re_fit_status === 'temporary_unfit');

        $result->update([
            'status'          => $this->re_fit_status,
            'workflow_status' => $isStillTemporary ? 'need_specialist' : 'reviewed',
            're_reviewed_by'  => auth()->id(),
            're_reviewed_at'  => now(),
            're_review_notes' => $this->re_review_notes,
        ]);

        // Jika dinyatakan FIT TO WORK atau FIT WITH NOTES
        if (in_array($this->re_fit_status, ['fit_to_work', 'fit_with_notes'])) {
            // Update riwayat & target jadwal MCU tahun depan (+1 Tahun)
            if ($result->mcu_master_data_id) {
                $master = McuMasterData::find($result->mcu_master_data_id);
                if ($master) {
                    $master->histories()->create([
                        'historical_date' => now(),
                        'notes'           => 'Hasil MCU Re-evaluasi (' . str_replace('_', ' ', $this->re_fit_status) . ') telah tuntas direview oleh dokter.'
                    ]);
                    $newTargetDate = \Carbon\Carbon::parse($master->mcu_date ?? now())->addYear();
                    $master->update([
                        'mcu_date'            => $newTargetDate,
                        'notification_status' => 'pending'
                    ]);
                }
            }

            // Generate Sertifikat Kelaikan Kerja
            try {
                $fullCertNumber = McuFitLetterService::generateCertificateNumber($result);
                $result->update([
                    'certificate_number' => $fullCertNumber,
                    'letter_status'      => 'draft',
                    'letter_updated_by'  => auth()->id(),
                    'letter_updated_at'  => now(),
                ]);
                $draftHtml = McuFitLetterService::generateDefaultHtml($result);
                $result->update(['letter_content' => $draftHtml]);
                McuFitLetterService::savePdfToStorage($result);
            } catch (\Exception $e) {
                Log::error('Gagal generate sertifikat MCU pada re-evaluasi: ' . $e->getMessage());
            }

            // Kirim notifikasi Fit to Work ke Karyawan & Dept Head
            $this->sendNotification($result);
        }

        activity('mcu')
            ->performedOn($result)
            ->causedBy(auth()->user())
            ->withProperties([
                'status'          => $this->re_fit_status,
                're_review_notes' => $this->re_review_notes,
            ])
            ->log('Dokter melakukan re-evaluasi hasil MCU setelah konsultasi spesialis');

        $this->showReReviewModal = false;
        $this->reset(['re_fit_status', 're_review_notes', 'reReviewResultId']);

        session()->flash('message', 'Re-evaluasi MCU berhasil disimpan. Status akhir: ' . strtoupper(str_replace('_', ' ', $this->re_fit_status)) . '. Sertifikat kelaikan kerja berhasil diterbitkan.');
        $this->activeTab = 'pending_doctor';
    }

    private function sendNotification(McuResult $result)
    {
        $employeeUser = $result->record?->employee;
        $deptHeadUser = $result->record?->deptHead;

        if ($employeeUser && $employeeUser->pilih_divisi === 'department') {
            $employeeUser->notifyNow(new McuResultNotification($result, 'employee', [\App\Channels\WhatsAppChannel::class, 'database']));
            try {
                $employeeUser->notify(new McuResultNotification($result, 'employee', ['mail']));
            } catch (\Exception $e) {
                Log::error('MCU Result Email Error (Employee): ' . $e->getMessage());
            }

            if ($deptHeadUser) {
                $deptHeadUser->notifyNow(new McuResultNotification($result, 'dept_head', [\App\Channels\WhatsAppChannel::class, 'database']));
                try {
                    $deptHeadUser->notify(new McuResultNotification($result, 'dept_head', ['mail']));
                } catch (\Exception $e) {
                    Log::error('MCU Result Email Error (Dept Head): ' . $e->getMessage());
                }
            }
        }
    }

    public function render()
    {
        $pendingReviews = McuResult::where('workflow_status', 'pending_doctor')
            ->with(['record.employee', 'diseaseCategories'])
            ->latest()
            ->get();

        $specialistReferrals = McuResult::where('workflow_status', 'need_specialist')
            ->with(['record.employee', 'diseaseCategories', 'reviewedBy'])
            ->latest()
            ->get();

        $readyReReviews = McuResult::where('workflow_status', 'pending_specialist_review')
            ->with(['record.employee', 'diseaseCategories', 'reviewedBy'])
            ->latest()
            ->get();

        return view('livewire.mcu.doctor-review', [
            'pendingReviews'      => $pendingReviews,
            'specialistReferrals' => $specialistReferrals,
            'readyReReviews'      => $readyReReviews,
            'diseaseCategories'   => DiseaseCategory::orderBy('name')->get(),
        ]);
    }
}
