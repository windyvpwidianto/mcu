<?php

namespace App\Livewire\Hazard;

use App\Models\Hazard;
use Livewire\Component;
use App\Enums\HazardStatus;
use App\Events\HazardStatusUpdated;

class HazardStatusUpdater extends Component
{
    public Hazard $hazard;


    public function update_Status($status)
    {

        $transitions = [
            'submitted' => ['in_progress'],
            'in_progress' => ['pending', 'closed'],
            'pending' => ['closed', 'cancelled'],
        ];

        $current = $this->hazard->status->value;      // enum → string
        $target = is_string($status) ? $status : $status->value; // pastikan $status juga string

        if (array_key_exists($current, $transitions) && in_array($target, $transitions[$current])) {
            $this->hazard->update([
                'status' => HazardStatus::from($target), // Simpan sebagai enum
            ]);

            event(new HazardStatusUpdated($this->hazard));
        } else {
            logger("🚫 Invalid transition from '$current' to '$target'");
        }
    }
    public function render()
    {
        return view('livewire.hazard.hazard-status-updater');
    }
}
