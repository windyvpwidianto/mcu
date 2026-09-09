<?php

namespace App\Livewire\Administration\WorkflowEvent;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\HazardWorkflow;
use Illuminate\Support\Facades\Route;

class Hazard extends Component
{
    use WithPagination;

    // Properti untuk menyimpan data formulir
    public $workflowId;
    public $from_status = '';
    public $from_inisial = '';
    public $to_status = '';
    public $to_inisial = '';
    public $role = '';

    // Properti untuk mengontrol modal
    public $isModalOpen = false;
    public function getRandomBadgeColor($status)
    {
        $map = [
            'cancelled'   => 'badge-error',   // Tulis lengkap
            'closed'      => 'badge-success',
            'in_progress' => 'badge-warning',
            'pending'     => 'badge-accent',
            'submitted'   => 'badge-info',
        ];

        return $map[$status] ?? 'badge-neutral';
    }
    // Daftar status dan peran yang mungkin (opsional, untuk dropdown)
    public $statusOptions = [
        'submitted',
        'pending',
        'in_progress',
        'closed',
        'cancelled'
    ];
    public $roleOptions = [
        'moderator',
        'erm'
    ];

    protected $rules = [
        'from_status' => 'required|string|max:255',
        'from_inisial' => 'required|string|max:255',
        'to_status' => 'required|string|max:255',
        'to_inisial' => 'required|string|max:255',
        'role' => 'required|string|in:moderator,erm',
    ];

    // Properti yang menentukan tab mana yang harus ditampilkan
    public $activeTab, $heaading, $subheading;

    public function mount()
    {
        $currentRoute = Route::currentRouteName();

        if ($currentRoute === 'hazard.workflows') {
            $this->activeTab = 'hazard';
            $this->heaading = 'Hazard Workflow Administration';
            $this->subheading = 'Manage hazard workflow transitions and roles';
        }
    }
    // Reset semua properti formulir
    public function resetForm()
    {
        $this->workflowId = null;
        $this->from_status = '';
        $this->from_inisial = '';
        $this->to_status = '';
        $this->to_inisial = '';
        $this->role = '';
    }

    // Buka modal dan reset form
    public function create()
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    // Buka modal dan isi form dengan data yang ada
    public function edit($id)
    {
        $workflow = HazardWorkflow::findOrFail($id);
        $this->workflowId = $id;
        $this->from_status = $workflow->from_status;
        $this->from_inisial = $workflow->from_inisial;
        $this->to_status = $workflow->to_status;
        $this->to_inisial = $workflow->to_inisial;
        $this->role = $workflow->role;

        $this->isModalOpen = true;
    }

    // Simpan atau update data
    public function save()
    {
        $this->validate();

        $data = [
            'from_status' => $this->from_status,
            'from_inisial' => $this->from_inisial,
            'to_status' => $this->to_status,
            'to_inisial' => $this->to_inisial,
            'role' => $this->role,
        ];

        if ($this->workflowId) {
            // Update
            HazardWorkflow::find($this->workflowId)->update($data);
            session()->flash('message', 'Workflow berhasil diupdate!');
        } else {
            // Create
            HazardWorkflow::create($data);
            session()->flash('message', 'Workflow berhasil ditambahkan!');
        }

        $this->closeModal();
        $this->resetForm();
    }

    // Hapus data
    public function delete($id)
    {
        HazardWorkflow::find($id)->delete();
        session()->flash('message', 'Workflow berhasil dihapus!');
    }
    // Tutup modal
    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }
    public function render()
    {
        return view('livewire.administration.workflow-event.hazard', [
            'workflows' => HazardWorkflow::paginate(20),
        ]);
    }
}
