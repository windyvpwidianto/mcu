<?php

namespace App\Livewire\Administration\People;

use App\Imports\ComplianceImport as CmpImport;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;

class ComplianceImport extends Component
{
    use WithFileUploads;

    public $file;

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
            Excel::import(new CmpImport, $path);

            // 3. Berhasil
            $this->dispatch('alert', [
                'text' => "Compliance imported successfully!",
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]);
            $this->file = null; // Reset file input

        } catch (\Exception $e) {
            // 4. Gagal
            $this->dispatch('alert', [
                'text' => "Compliance import failed: " . $e->getMessage(),
                'duration' => 5000,
                'destination' => '/contact',
                'newWindow' => true,
                'close' => true,
                'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
            ]);

            // Opsional: Log error untuk debugging
            // \Log::error('Compliance Import Error: ' . $e->getMessage());
        }
    }
    public function closeModal()
    {

        $this->file = null;
        $this->resetErrorBag();
    }
    public function render()
    {
        return view('livewire.administration.people.compliance-import');
    }
}
