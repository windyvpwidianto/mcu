<?php

namespace App\Livewire\Mcu;

use App\Models\McuParticipant;
use App\Models\McuResult; // Pastikan Anda sudah membuat Model ini
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class InputResult extends Component
{
    use WithFileUploads;
    use WithPagination;

    public $participant_id = null;
    public $result_document;
    public $admin_notes;
    public $searchParticipant = '';
    public $showParticipantDropdown = false;

    public function rules()
    {
        return [
            'participant_id' => 'required|exists:mcu_participants,id',
            'result_document' => 'required|mimes:pdf,jpg,jpeg,png|max:5120', // Max 5MB
            'admin_notes' => 'nullable|string',
        ];
    }

    public function saveResult()
    {
        $this->validate();

        // Upload File
        $path = $this->result_document->store('mcu_results', 'public');

        // Simpan ke database dengan status langsung 'pending_review' (Sesuai flowchart)
        McuResult::create([
            'mcu_participant_id' => $this->participant_id,
            'result_document' => $path,
            'admin_notes' => $this->admin_notes,
            'workflow_status'    => 'pending_doctor', // Mengisi status alur kerja
            'status'             => null,             // Status medis dikosongkan dulu karena belum direview dokter
        ]);

        session()->flash('message', 'Hasil MCU berhasil diunggah. Menunggu review Dokter.');
        $this->reset(['participant_id', 'result_document', 'admin_notes', 'searchParticipant']);
    }

    public function updatedSearchParticipant()
    {
        $this->showParticipantDropdown = true;

        // Opsional: Reset ID jika user mengetik ulang pencarian
        if (strlen($this->searchParticipant) > 0) {
            $this->participant_id = null;
        }
        $this->resetPage();
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }

    /**
     * Trigger saat user mengklik salah satu opsi di dropdown
     */
    public function selectParticipant($id, $name)
    {
        $this->participant_id = $id;
        $this->searchParticipant = $name; // Ubah teks input menjadi nama yang dipilih
        $this->showParticipantDropdown = false; // Tutup dropdown
    }

    public function render()
    {
        $today = Carbon::today();
        // 1. Mulai query dasar: Ambil peserta yang belum memiliki hasil MCU
        $query = McuParticipant::whereDoesntHave('result')
            ->with(['employee', 'schedule'])
            ->whereHas('schedule', function ($q) use ($today) {
                $q->whereDate('schedule_date', '>=', $today);
            });

        // 3. Tambahkan filter pencarian (Jika user mengetik di input pencarian)
        if (!empty($this->searchParticipant) && empty($this->participant_id)) {
            $query->whereHas('employee', function ($q) {
                $q->where('name', 'like', '%' . $this->searchParticipant . '%');
            });
        }

        // 4. Ambil data dan format menjadi bentuk Array yang dibutuhkan komponen
        $formattedParticipants = $query->paginate(30)->through(function ($p) {
            // Karena Anda menggunakan format(), pastikan schedule_date di model McuSchedule sudah di-cast menjadi 'datetime' atau 'date'
            $date = $p->schedule ? $p->schedule->schedule_date->format('d M Y') : 'Tanpa Jadwal';

            return (object) [
                'id'            => $p->id,
                'name'          => $p->employee->name . ' - ' . $date, // Untuk Searchable Select
                'raw_name'      => $p->employee->name,                 // Untuk Tabel
                'schedule_date' => $date,                              // Untuk Tabel
                'status'        => $p->notification_status ?? 'pending' // (Opsional) Ambil status dari DB
            ];
        });

        // 4. Kirim data yang sudah diformat ke view
        return view('livewire.mcu.input-result', [
            'formattedParticipants' => $formattedParticipants
        ]);
    }
}
