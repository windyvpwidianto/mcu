<?php

namespace App\Livewire\Administration\WorkflowEvent;

use Livewire\Component;
use App\Models\WpiWorkflow;
use Livewire\WithPagination;

class WpiWorkflowManager extends Component
{
    use WithPagination;

    // Properti UI
    public $activeTab = 'wpi-workflow';
    public $heaading = 'WPI Workflow Management';
    public $subheading = 'Kelola aturan transisi status untuk modul WPI';
    public $isModalOpen = false;

    // Properti Form
    public $workflowId, $role, $from_status, $to_status, $from_inisial, $to_inisial;

    // Opsi Dropdown
    public $statusOptions = ['Submitted', 'Assigned', 'Pending', 'Closed', 'Cancelled', 'Final Review', 'Review Event'];
    public $roleOptions = ['Submitter', 'Event Report Manager', 'Moderator'];
    public function render()
    {
        return view('livewire.administration.workflow-event.wpi-workflow-manager',[
            'workflows' => WpiWorkflow::latest()->paginate(20)
        ]);
    }

    public function openModal()
    {
        $this->resetErrorBag();
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['workflowId', 'role', 'from_status', 'to_status', 'from_inisial', 'to_inisial']);
    }

    public function edit($id)
    {
        $workflow = WpiWorkflow::findOrFail($id);
        $this->workflowId = $workflow->id;
        $this->role = $workflow->role;
        $this->from_status = $workflow->from_status;
        $this->to_status = $workflow->to_status;
        $this->from_inisial = $workflow->from_inisial;
        $this->to_inisial = $workflow->to_inisial;

        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate([
            'role' => 'required',
            'from_status' => 'required',
            'to_status' => 'required',
            'from_inisial' => 'required',
            'to_inisial' => 'required',
        ]);

        WpiWorkflow::updateOrCreate(['id' => $this->workflowId], [
            'role' => $this->role,
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'from_inisial' => $this->from_inisial,
            'to_inisial' => $this->to_inisial,
        ]);

        $this->closeModal();
        session()->flash('success', 'Workflow berhasil disimpan.');
        // Jika menggunakan x-toast, pastikan x-toast mendengarkan flash message ini
    }

    public function delete($id)
    {
        WpiWorkflow::destroy($id);
        session()->flash('success', 'Workflow berhasil dihapus.');
    }

    // Helper untuk warna badge di view
    public function getRandomBadgeColor($status)
    {
        return match ($status) {
            'Submitted' => 'badge-info',
            'Final Review'   => 'badge-warning',
            'Closed'    => 'badge-success',
            'Cancelled' => 'badge-error',
            'Assigned' => 'badge-primary',
            'Review Event' => 'badge-primary',
            default     => 'badge-ghost',
        };
    }
}
