<?php

namespace App\Livewire\Administration\RiskMatrix;

use Livewire\Component;
use App\Models\RiskMatrixCell;
use App\Livewire\Administration\RiskMatrix\Grid;

class Form extends Component
{
     public $idCell, $likelihood_id, $risk_consequence_id, $severity, $description, $action;

    protected $listeners = ['edit-cell' => 'loadCell'];

    public function loadCell($id, $likelihood, $consequence)
    {
        $this->idCell = $id;
        $this->likelihood_id = $likelihood;
        $this->risk_consequence_id = $consequence;

        if ($id) {
            $cell = RiskMatrixCell::find($id);
            $this->severity = $cell->severity;
            $this->description = $cell->description;
            $this->action = $cell->action;
        } else {
            $this->severity = null;
            $this->description = null;
            $this->action = null;
        }
    }

    public function save()
    {
        $this->validate([
            'likelihood_id' => 'required',
            'risk_consequence_id' => 'required',
            'severity' => 'required',
        ]);

        RiskMatrixCell::updateOrCreate(
            [
                'likelihood_id' => $this->likelihood_id,
                'risk_consequence_id' => $this->risk_consequence_id,
            ],
            [
                'severity' => $this->severity,
                'description' => $this->description,
                'action' => $this->action,
            ]
        );

        $this->dispatch('refresh')->to(Grid::class);
        $this->reset();
    }
    public function render()
    {
        return view('livewire.administration.risk-matrix.form');
    }
}
