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
    public $emails = [''];

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
        
        $existingEmails = $contractor->emails;
        $this->emails = !empty($existingEmails) ? $existingEmails : [''];

        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function addEmail()
    {
        $this->emails[] = '';
    }

    public function removeEmail($index)
    {
        unset($this->emails[$index]);
        $this->emails = array_values($this->emails);
        if (empty($this->emails)) {
            $this->emails = [''];
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->reset(['selectedContractorId', 'selectedContractorName']);
        $this->emails = [''];
        $this->resetErrorBag();
    }

    public function saveEmail()
    {
        $this->resetErrorBag();

        if (!$this->selectedContractorId) {
            return;
        }

        $contractor = Contractor::find($this->selectedContractorId);
        if (!$contractor) {
            $this->closeEditModal();
            return;
        }

        // Expand any comma, semicolon, or newline separated emails
        $parsedList = [];
        $hasErrors = false;

        foreach ($this->emails as $idx => $rawInput) {
            $trimmed = trim($rawInput ?? '');
            if ($trimmed === '') {
                continue;
            }

            // Split in case user pasted multiple emails into a single input
            $parts = preg_split('/[\s,;]+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($parts as $part) {
                $emailItem = strtolower(trim($part));
                if (!filter_var($emailItem, FILTER_VALIDATE_EMAIL)) {
                    $this->addError("emails.{$idx}", "Alamat email '{$part}' tidak valid.");
                    $hasErrors = true;
                } else {
                    $parsedList[] = $emailItem;
                }
            }
        }

        if ($hasErrors) {
            return;
        }

        // Remove duplicates while preserving order
        $uniqueEmails = array_values(array_unique($parsedList));

        $emailString = !empty($uniqueEmails) ? implode(', ', $uniqueEmails) : null;

        $contractor->update([
            'email' => $emailString,
        ]);

        $count = count($uniqueEmails);
        $msgText = $count > 0 
            ? "{$count} email notifikasi untuk {$contractor->contractor_name} berhasil diperbarui."
            : "Email notifikasi untuk {$contractor->contractor_name} berhasil dikosongkan.";

        $this->dispatch('alert', [
            'text' => $msgText,
            'duration' => 3500,
            'close' => true,
            'backgroundColor' => 'linear-gradient(to right, #10b981, #059669)',
        ]);

        session()->flash('success', $msgText);

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
