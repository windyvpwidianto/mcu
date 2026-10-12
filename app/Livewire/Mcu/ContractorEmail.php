<?php

namespace App\Livewire\Mcu;

use App\Models\Contractor;
use Livewire\Component;
use Livewire\WithPagination;

class ContractorEmail extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = 'all'; // all, enabled, disabled
    public $filterEmail = 'all';  // all, has_email, no_email
    public $perPage = 15;

    // Modal Edit State
    public $showEditModal = false;
    public $selectedContractorId = null;
    public $selectedContractorName = '';
    public $email = '';

    protected $rules = [
        'email' => 'nullable|email:filter|max:255',
    ];

    protected $messages = [
        'email.email' => 'Format alamat email tidak valid.',
        'email.max'   => 'Alamat email maksimal 255 karakter.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterEmail()
    {
        $this->resetPage();
    }

    public function openEditModal($id)
    {
        $contractor = Contractor::find($id);
        if (!$contractor) {
            $this->dispatch('alert', [
                'text' => 'Kontraktor tidak ditemukan.',
                'backgroundColor' => 'linear-gradient(to right, #f59e0b, #ef4444)',
            ]);
            return;
        }

        $this->selectedContractorId = $contractor->id;
        $this->selectedContractorName = $contractor->contractor_name;
        $this->email = $contractor->email ?? '';
        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->reset(['selectedContractorId', 'selectedContractorName', 'email']);
        $this->resetErrorBag();
    }

    public function saveEmail()
    {
        $this->validate();

        if (!$this->selectedContractorId) {
            return;
        }

        $contractor = Contractor::find($this->selectedContractorId);
        if ($contractor) {
            $contractor->update([
                'email' => !empty(trim($this->email)) ? trim($this->email) : null,
            ]);

            $this->dispatch('alert', [
                'text' => "Email notifikasi untuk {$contractor->contractor_name} berhasil diperbarui.",
                'duration' => 3500,
                'close' => true,
                'backgroundColor' => 'linear-gradient(to right, #10b981, #059669)',
            ]);

            session()->flash('success', "Email notifikasi untuk {$contractor->contractor_name} berhasil disimpan.");
        }

        $this->closeEditModal();
    }

    public function clearEmail($id)
    {
        $contractor = Contractor::find($id);
        if ($contractor) {
            $contractor->update(['email' => null]);

            $this->dispatch('alert', [
                'text' => "Email untuk {$contractor->contractor_name} telah dihapus.",
                'duration' => 3000,
                'close' => true,
                'backgroundColor' => 'linear-gradient(to right, #f59e0b, #d97706)',
            ]);

            session()->flash('success', "Email untuk {$contractor->contractor_name} berhasil dihapus.");
        }
    }

    public function render()
    {
        $query = Contractor::query()
            ->withCount('users')
            ->when(!empty(trim($this->search)), function ($q) {
                $term = trim($this->search);
                $q->where(function ($sub) use ($term) {
                    $sub->where('contractor_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->when($this->filterStatus !== 'all', function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->when($this->filterEmail === 'has_email', function ($q) {
                $q->whereNotNull('email')->where('email', '!=', '');
            })
            ->when($this->filterEmail === 'no_email', function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('email')->orWhere('email', '');
                });
            })
            ->orderBy('contractor_name', 'asc');

        $contractors = $query->paginate($this->perPage);

        // Stats
        $statTotal = Contractor::count();
        $statWithEmail = Contractor::whereNotNull('email')->where('email', '!=', '')->count();
        $statWithoutEmail = $statTotal - $statWithEmail;
        $statActive = Contractor::where('status', 'enabled')->count();

        return view('livewire.mcu.contractor-email', [
            'contractors' => $contractors,
            'stats' => [
                'total'         => $statTotal,
                'with_email'    => $statWithEmail,
                'without_email' => $statWithoutEmail,
                'active'        => $statActive,
            ],
        ]);
    }
}
