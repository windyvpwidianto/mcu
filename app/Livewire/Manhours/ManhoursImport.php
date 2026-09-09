<?php

namespace App\Livewire\Manhours;

use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ManhoursImport as ManhoursImportClass;

class ManhoursImport extends Component
{
    use WithFileUploads;

    public $file;
    public $showModal = '';

    public function import()
    {
        // 1. Validasi file
        $this->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Maks 10MB
        ], [
            'file.required' => 'File harus diunggah.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file terlalu besar (maks 10MB).'
        ]);

        try {
            // 2. Simpan file sementara dan proses import
            $path = $this->file->getRealPath();

            // Menggunakan Laravel Excel untuk mengimpor data
            Excel::import(new ManhoursImportClass, $path);

            // 3. Berhasil
            $this->dispatch('alert', [
                'text' => "Manhours imported successfully!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]);
            $this->file = null; // Reset file input
            $this->showModal = "";
        } catch (\Exception $e) {
            // 4. Gagal
            $this->dispatch('alert', [
                'text' => "manhours import failed: " . $e->getMessage(),
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]);
            $this->showModal = "modal-open";
            // Opsional: Log error untuk debugging
            // \Log::error('Manhours Import Error: ' . $e->getMessage());
        }
    }
    // Method untuk membuka modal dari tombol di luar
    public function openModal()
    {
        $this->showModal = "modal-open";
    }

    // Method untuk menutup modal
    public function closeModal()
    {
        $this->showModal = "";
        // Opsional: reset file dan error saat menutup
        $this->file = null;
        $this->resetErrorBag();
    }
    public function render()
    {
        return view('livewire.manhours.manhours-import');
    }
}
