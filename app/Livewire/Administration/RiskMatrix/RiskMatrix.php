<?php

namespace App\Livewire\Administration\RiskMatrix;

use Livewire\Component;
use App\Models\Likelihood;
use App\Models\RiskConsequence;

class RiskMatrix extends Component
{
     public $likelihoods, $consequences;

    public function mount()
    {
        $this->likelihoods = Likelihood::orderByDesc('level')->get(); // L5 - L1
        $this->consequences = RiskConsequence::orderBy('level')->get(); // C1 - C5
    }
    public function render()
    {
        return view('livewire.administration.risk-matrix.risk-matrix');
    }
}
