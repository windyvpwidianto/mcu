<?php

namespace App\Livewire\Hazard;

use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\HazardImport;
use Livewire\WithFileUploads;

class ImportHazardReportsModal extends Component
{
    use WithFileUploads;

    public $file;
    public bool $open = false;

    public function openModal()
    {
        $this->reset(['file']);
        $this->open = true;
    }

    public function closeModal()
    {
        $this->open = false;
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:102400',
        ]);

        Excel::import(new HazardImport, $this->file->getRealPath());

        $this->dispatch('import-success'); // event ke parent jika perlu refresh tabel
        $this->dispatch(
            'alert',
            [
               'text' => "data berhasil diimport!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]
        );
        $this->reset(['file']);
        $this->closeModal();
    }
    public function render()
    {
        return view('livewire.hazard.import-hazard-reports-modal');
    }
}
