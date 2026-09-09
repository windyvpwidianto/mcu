<?php

namespace App\Livewire\Administration\RiskMatrix;

use Flux\Flux;
use Livewire\Component;
use App\Models\Likelihood;
use App\Models\RiskAssessment;
use App\Models\RiskMatrixCell;
use App\Models\RiskConsequence;
use App\Models\RiskAssessmentMatrix;

class Grid extends Component
{
    public $likelihoods, $consequences,$severity, $description, $action;
    public $editingCellId = null;

    public function mount()
    {
        $this->likelihoods = Likelihood::orderByDesc('level')->get();
        $this->consequences = RiskConsequence::orderBy('level')->get();
    }

    public function edit($likelihoodId, $consequenceId)
    {
        $likelihoods_level = Likelihood::whereId($likelihoodId)->first()->level;
        $RiskConsequence_level = RiskConsequence::whereId($consequenceId)->first()->level;
        $cell = RiskMatrixCell::where('likelihood_id', $likelihoodId)
            ->where('risk_consequence_id', $consequenceId)
            ->first();
        $this->editingCellId = $cell?->id;
        $this->severity = $cell?->severity;
        $this->description = "Auto generated L $likelihoods_level × C $RiskConsequence_level";
        $this->action = $cell?->action;
        $this->dispatch('edit-cell', id: $cell?->id, likelihood: $likelihoodId, consequence: $consequenceId);
         Flux::modal('RiskMatrix')->show();
    }
    public function updateMatrix()
    {
        $this->validate([
           
            'severity' => 'required',
        ]);

        RiskMatrixCell::updateOrCreate(
            [
                'id' =>  $this->editingCellId,
            ],
            [
                'severity' => $this->severity,
                'description' => $this->description,
                'action' => $this->action,
            ]
        );
         $risk_assessment_id =  RiskAssessment::where('name', 'like', '%' . $this->severity . '%')->first()->id;
        RiskAssessmentMatrix::updateOrCreate(
            [
                'risk_matrix_cell_id' =>  $this->editingCellId,
            ],
            [
                'risk_assessment_id' =>  $risk_assessment_id,
            ]
        );
         $this->dispatch(
            'alert',
            [
                'text'            => "data berhasil di edit!!!",
                'duration'        => 5000,
                'destination'     => '/contact',
                'newWindow'       => true,
                'close'           => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]
        );
        Flux::modal('RiskMatrix')->close();
    }
    public function close_modal()
    {
        Flux::modal('RiskMatrix')->close();
    }
    public function render()
    {
        return view('livewire.administration.risk-matrix.grid');
    }
}
