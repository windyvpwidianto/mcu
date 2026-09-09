<?php

namespace App\Livewire\Hazard;

use App\Models\User;
use App\Models\Hazard;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Notifications\HazardSubmittedNotification;

class AssignErmModal extends Component
{
    public $selectedErms = [], $hazardId, $ermList;
    public function mount($hazard)
    {
        $this->hazardId = $hazard;
        $hazard = Hazard::findOrFail($hazard);

        $assignmentUserIds = DB::table('erm_assignments')
            ->select('user_id')
            ->where(function ($q) use ($hazard) {
                $q->orWhere('department_id', $hazard->department_id)
                    ->orWhere('contractor_id', $hazard->contractor_id);
            })
            ->pluck('user_id');

        // ✅ Inisialisasi ermList
        $this->ermList = User::whereIn('id', $assignmentUserIds)->get();
    }
    public function assign()
    {
        if (count($this->selectedErms) === 0) {
            throw ValidationException::withMessages([
                'selectedErms' => 'Pilih minimal 1 ERM.'
            ]);
        }

        if (count($this->selectedErms) > 2) {
            throw ValidationException::withMessages([
                'selectedErms' => 'Maksimal hanya 2 ERM yang dapat dipilih.'
            ]);
        }

        $hazard = Hazard::findOrFail($this->hazardId);
        $hazard->assignedErms()->sync($this->selectedErms);

        $hazard->status = 'in_progress';
        $hazard->save();

        foreach ($hazard->assignedErms as $erm) {
            $erm->notify(new HazardSubmittedNotification($hazard));
        }

        $this->dispatch('ermAssigned');
        session()->flash('message', 'Laporan dikirim ke ERM terpilih.');
    }
    public function render()
    {
        return view('livewire.hazard.assign-erm-modal');
    }
}
