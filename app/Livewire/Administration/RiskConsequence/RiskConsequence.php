<?php

namespace App\Livewire\Administration\RiskConsequence;

use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RiskConsequence as RiskConsequences;

class RiskConsequence extends Component
{
    use WithPagination;

    public $level, $name, $description, $riskConsequenceId;
    public $isEditing = false;
    protected $rules = [
        'level' => 'required',
        'name' => 'required',
        'description' => 'required',
    ];
    public function openModal()
    {
        Flux::modal('Risk-Consequence')->show();
    }
    public function close_modal()
    {
        if ($this->isEditing) {
            Flux::modal('Risk-ConsequenceEdit')->close();
        } else {
            Flux::modal('Risk-Consequence')->close();
        }
        $this->resetForm();
    }
    public function store()
    {
        $this->validate();

        RiskConsequences::create([
            'level' => $this->level,
            'name' => $this->name,
            'description' => $this->description,
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

        Flux::modal('Risk-ConsequenceEdit')->show();
        $risk = RiskConsequences::findOrFail($id);
        $this->riskConsequenceId = $risk->id;
        $this->level = $risk->level;
        $this->name = $risk->name;
        $this->description = $risk->description;
        $this->isEditing = true;
    }

    public function update()
    {
        $this->validate();

        $risk = RiskConsequences::findOrFail($this->riskConsequenceId);
        $risk->update([
            'level' => $this->level,
            'name' => $this->name,
            'description' => $this->description,
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
        RiskConsequences::destroy($id);
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
        $this->reset(['level', 'name', 'description', 'riskConsequenceId', 'isEditing']);
    }
    public function render()
    {
        return view('livewire.administration.risk-consequence.risk-consequence',[
            'risks' => RiskConsequences::orderBy('level')->paginate(10),
        ]);
    }
}
