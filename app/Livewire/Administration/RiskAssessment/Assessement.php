<?php

namespace App\Livewire\Administration\RiskAssessment;

use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RiskAssessment;

class Assessement extends Component
{
    use WithPagination;
    public $name, $action_days, $coordinator, $reporting_obligation, $notes, $riskAssessmentId;
    public $isEditing = false;
    protected $rules = [
        'name' => 'required',
        'action_days' => 'required',
        'coordinator' => 'required',
        'reporting_obligation' => 'required',
        'notes' => 'required',
    ];
     public function openModal()
    {
        Flux::modal('Risk-Assessment')->show();
    }
    public function close_modal()
    {
        if ($this->isEditing) {
            Flux::modal('Risk-AssessmentEdit')->close();
        } else {
            Flux::modal('Risk-Assessment')->close();
        }
        $this->resetForm();
    }
    public function store()
    {
        $this->validate();

        RiskAssessment::create([
            'name' => $this->name,
            'action_days' => $this->action_days,
            'coordinator' => $this->coordinator,
            'reporting_obligation' => $this->reporting_obligation,
            'notes' => $this->notes,
        ]);
        $this->dispatch(
            'alert',
            [
                'text'            => "data berhasil di buat!!!",
                'duration'        => 5000,
                'destination'     => '/contact',
                'newWindow'       => true,
                'close'           => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]
        );
        $this->resetForm();
    }

    public function edit_modal($id)
    {

        Flux::modal('Risk-AssessmentEdit')->show();
        $risk = RiskAssessment::findOrFail($id);
        $this->riskAssessmentId = $risk->id;
        $this->name = $risk->name;
        $this->action_days = $risk->action_days;
        $this->coordinator = $risk->coordinator;
        $this->reporting_obligation = $risk->reporting_obligation;
        $this->notes = $risk->notes;
        $this->isEditing = true;
    }

    public function update()
    {
        $this->validate();

        $risk = RiskAssessment::findOrFail($this->riskAssessmentId);
        $risk->update([
            'name' => $this->name,
            'action_days' => $this->action_days,
            'coordinator' => $this->coordinator,
            'reporting_obligation' => $this->reporting_obligation,
            'notes' => $this->notes,
        ]);
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
       
    }

    public function delete($id)
    {
        RiskAssessment::destroy($id);
        $this->dispatch(
            'alert',
            [
                'text' => "Data berhasil di hapus!!!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #ff3333, #ff6666)",
            ]
        );
    }

    public function resetForm()
    {
        $this->reset(['name', 'action_days', 'coordinator','reporting_obligation','notes', 'riskAssessmentId', 'isEditing']);
    }
    public function render()
    {
        return view('livewire.administration.risk-assessment.assessement',[
             'risks' => RiskAssessment::paginate(10),
        ]);
    }
}
