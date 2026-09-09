<?php

namespace App\Livewire\Administration\CauseAnalysis;

use Livewire\Component;
use App\Models\UnsafeCondition;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;

class Kta extends Component
{
    #[Validate('required|string')]
    public $name;
    #[Validate('required|string')]
    public $status;
    public $ktaId; // untuk edit
    public $modalOpen = false;
    protected $messages = [

        'name.required'     => 'Nama wajib diisi.',
        'status.required'     => 'Status wajib diisi.',
    ];
    protected function rules()
    {
        return [
            'name'   => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['Enabled', 'Disable'])],
        ];
    }

    public function create()
    {
        $this->reset(['name', 'status', 'ktaId']);
        $this->modalOpen = true;
    }
    public function store()
    {
        $this->validate();

        UnsafeCondition::create([
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
        $kta = UnsafeCondition::findOrFail($id);

        $this->ktaId = $kta->id;
        $this->name  = $kta->name;
        $this->status = $kta->status;

        $this->modalOpen = true;
    }

    public function update()
    {
        $this->validate();

        UnsafeCondition::where('id', $this->ktaId)->update([
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
        $this->reset(['name', 'status', 'ktaId', 'modalOpen']);
    }
    public function delete($id)
    {
        UnsafeCondition::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.administration.cause-analysis.kta', [
            'ktas' => UnsafeCondition::latest()->get(),
        ]);
    }
}
