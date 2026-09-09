<?php

namespace App\Livewire\Hazard;

use App\Models\Hazard;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ProgressForm extends Component
{
    public Hazard $hazard;
    public $effectiveRole;
    public $proceedTo = '';
    public $assignTo1 = '';
    public $assignTo2 = '';
    public $ermList = [];

    public function mount(Hazard $hazard, $effectiveRole)
    {
        $this->hazard = $hazard;
        $this->effectiveRole = $effectiveRole;

        $assignmentUserIds = DB::table('erm_assignments')
            ->where(function ($q) use ($hazard) {
                $q->orWhere('department_id', $hazard->department_id)
                  ->orWhere('contractor_id', $hazard->contractor_id);
            })
            ->pluck('user_id');

        $this->ermList = \App\Models\User::whereIn('id', $assignmentUserIds)->get();
    }

    public function processAction()
    {
        if ($this->proceedTo === 'in_progress') {
            $this->hazard->assignedErms()->sync(array_filter([$this->assignTo1, $this->assignTo2]));
        }

        $this->hazard->status = $this->proceedTo;
        $this->hazard->save();

        session()->flash('message', 'Status berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.hazard.progress-form');
    }
}
