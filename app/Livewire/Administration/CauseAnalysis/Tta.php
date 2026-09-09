<?php

namespace App\Livewire\Administration\CauseAnalysis;

use App\Models\UnsafeAct;
use Livewire\Component;
use Illuminate\Validation\Rule;

class Tta extends Component
{
    public $name;
    public $status;
    public $ttaId; // untuk edit
    public $modalOpen = false;

    protected function rules()
    {
        return [
            'name'   => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['Enabled', 'Disable'])],
        ];
    }

    public function create()
    {
        $this->reset(['name', 'status', 'ttaId']);
        $this->modalOpen = true;
    }
    public function store()
    {
        $this->validate();

        UnsafeAct::create([
            'name'   => $this->name,
            'status' => $this->status,
        ]);
        $this->dispatch(
            'alert',
            [
                'text' => 'Data berhasil di input!!!',
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]
        );
        $this->closeModal();
    }

    public function edit($id)
    {
        $tta = UnsafeAct::findOrFail($id);

        $this->ttaId = $tta->id;
        $this->name  = $tta->name;
        $this->status = $tta->status;

        $this->modalOpen = true;
    }

    public function update()
    {
        $this->validate();

        UnsafeAct::where('id', $this->ttaId)->update([
            'name'   => $this->name,
            'status' => $this->status,
        ]);
        $this->dispatch(
            'alert',
            [
                'text' => 'Data berhasil di edit!!!',
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "linear-gradient(to right, #06b6d4, #22c55e)",
            ]
        );
        $this->closeModal();
        $this->reset();
    }
    public function closeModal()
    {
        $this->reset(['name', 'status', 'ttaId', 'modalOpen']);
    }
    public function delete($id)
    {
        UnsafeAct::findOrFail($id)->delete();
    }
    public function render()
    {
        return view('livewire.administration.cause-analysis.tta', [
            'ttas' => UnsafeAct::latest()->get(),
        ]);
    }
}
