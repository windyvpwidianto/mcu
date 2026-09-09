<?php

namespace App\Livewire\Mcu;

use App\Models\McuResult;
use App\Models\DiseaseCategory;
use App\Notifications\McuResultNotification;
use Livewire\Component;

class DoctorReview extends Component
{
    public $selectedResultId;
    public $fit_status;
    public $doctor_notes;

    public $selectedDiseaseCategories = [];
    public $showReviewModal = false;

    // Properti untuk input inline
    public $new_disease_name;

    public function openReviewModal($id)
    {
        $this->resetValidation();
        $this->reset(['fit_status', 'doctor_notes', 'selectedDiseaseCategories', 'new_disease_name']);

        $this->selectedResultId = $id;

        $result = McuResult::with('diseaseCategories')->find($id);
        if ($result) {
            $this->selectedDiseaseCategories = $result->diseaseCategories->pluck('id')->toArray();
        }

        $this->showReviewModal = true;
    }

    // --- FUNGSI SIMPAN PENYAKIT DARI INPUT INLINE ---
    public function saveNewDisease()
    {
        $this->validate([
            'new_disease_name' => 'required|string|min:3|unique:disease_categories,name',
        ], [
            'new_disease_name.required' => 'Nama wajib diisi.',
            'new_disease_name.unique'   => 'Penyakit sudah ada.',
            'new_disease_name.min'      => 'Min. 3 huruf.',
        ]);

        // 1. Simpan ke database
        $newCategory = DiseaseCategory::create([
            'name' => trim($this->new_disease_name)
        ]);

        // 2. Hapus 'tambah_penyakit' dari array agar kolom ketik otomatis tertutup (UX lebih rapi)
        $this->selectedDiseaseCategories = array_diff($this->selectedDiseaseCategories, ['tambah_penyakit']);

        // 3. Masukkan ID penyakit yang baru saja dibuat agar langsung tercentang
        $this->selectedDiseaseCategories[] = (string) $newCategory->id;

        // 4. Reset input teks
        $this->new_disease_name = '';
        $this->resetValidation('new_disease_name');
    }

    public function saveReview()
    {
        $this->validate([
            'fit_status'        => 'required|in:fit_to_work,fit_with_notes,temporary_unfit,unfit',
            'doctor_notes'      => 'nullable|string',
        ]);

        if (!$this->selectedResultId) {
            session()->flash('error', 'Data MCU tidak ditemukan.');
            return;
        }

        $result = McuResult::with(['participant.employee', 'participant.deptHead'])->find($this->selectedResultId);

        if ($result) {
            $result->update([
                'status'              => $this->fit_status,
                'workflow_status'     => 'reviewed',
                'doctor_site_consult' => null,
                'follow_up_date'      => null,
                'doctor_notes'        => $this->doctor_notes,
                'reviewed_by'         => auth()->id(),
                'reviewed_at'         => now(),
            ]);

            // PENTING: Filter array agar string 'tambah_penyakit' tidak ikut masuk ke tabel pivot MySQL!
            // Hanya ambil nilai yang berupa angka (ID penyakit asli)
            $validIds = array_filter($this->selectedDiseaseCategories, function ($id) {
                return $id !== 'tambah_penyakit' && is_numeric($id);
            });

            // Sync menggunakan ID yang sudah bersih dari string non-numeric
            $result->diseaseCategories()->sync($validIds);

            // ... (KODE NOTIFIKASI DAN RESET FORM ANDA DI BAWAHNYA TETAP SAMA) ...
            $employeeUser = $result->participant?->employee;
            $deptHeadUser = $result->participant?->deptHead;

            if ($employeeUser) {
                // Notifikasi WhatsApp dan Database dikirim langsung (tanpa antrean/defer)
                $employeeUser->notifyNow(new McuResultNotification($result, 'employee', [\App\Channels\WhatsAppChannel::class, 'database']));
                // Notifikasi Email dikirim ke antrean (defer/queue)
                try {
                    $employeeUser->notify(new McuResultNotification($result, 'employee', ['mail']));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('MCU Result Email Error (Employee): ' . $e->getMessage());
                }
            }

            if ($deptHeadUser) {
                // Notifikasi WhatsApp dan Database dikirim langsung
                $deptHeadUser->notifyNow(new McuResultNotification($result, 'dept_head', [\App\Channels\WhatsAppChannel::class, 'database']));
                // Notifikasi Email dikirim ke antrean (defer/queue)
                try {
                    $deptHeadUser->notify(new McuResultNotification($result, 'dept_head', ['mail']));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('MCU Result Email Error (Dept Head): ' . $e->getMessage());
                }
            }

            $this->showReviewModal = false;
            $this->reset(['fit_status', 'doctor_notes', 'selectedDiseaseCategories', 'selectedResultId']);

            session()->flash('message', 'Review MCU berhasil disimpan dan notifikasi hasil telah terkirim.');
        }
    }

    public function render()
    {
        $pendingReviews = McuResult::where('workflow_status', 'pending_doctor')
            ->with(['participant.employee', 'diseaseCategories'])
            ->get();

        return view('livewire.mcu.doctor-review', [
            'pendingReviews'    => $pendingReviews,
            'diseaseCategories' => DiseaseCategory::orderBy('name')->get(),
        ]);
    }
}
