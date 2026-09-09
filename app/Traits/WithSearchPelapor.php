<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait WithSearchPelapor
{
    /**
     * Properti yang dibutuhkan oleh Trait
     * Variabel ini akan otomatis tersedia di Class yang menggunakan Trait ini.
     */
    public $searchPelapor = '';
    public $pelapors = [];
    public $showPelaporDropdown = false;
    public $manualPelaporMode = false;
    public $manualPelaporName;

    /**
     * Livewire Magic Method: Dijalankan otomatis saat komponen dimuat (mount).
     * Ini akan mengisi data pelapor secara otomatis jika user sudah login.
     */
    /**
     * Livewire Magic Method: Dijalankan otomatis saat komponen dimuat (mount).
     */
    public function mountWithSearchPelapor()
    {
        // Cek apakah properti sudah terisi (berarti ini mode Update)
        // Jika masih kosong, baru isi dengan Auth user (mode Create)
        if (empty($this->searchPelapor) && empty($this->manualPelaporName)) {
            if (Auth::check()) {
                if (property_exists($this, 'pelapor_id')) {
                    $this->pelapor_id = Auth::id();
                }
                $this->searchPelapor = Auth::user()->name;
            }
        }
    }

    /**
     * Method bantuan untuk memuat data pelapor dari database saat Edit
     */
    public function loadInitialPelapor($id, $manualName = null)
    {
        if ($manualName) {
            $this->manualPelaporMode = true;
            $this->manualPelaporName = $manualName;
            $this->searchPelapor = $manualName;
            $this->pelapor_id = null;
        } else {
            $user = User::find($id);
            if ($user) {
                $this->pelapor_id = $user->id;
                $this->searchPelapor = $user->name;
                $this->manualPelaporMode = false;
            }
        }
    }

    /**
     * Menangani pencarian pelapor saat user mengetik.
     */
    public function updatedSearchPelapor()
    {
        // 1. Reset data lama
        $this->pelapor_id = null;
        $this->manualPelaporMode = false;
        $this->manualPelaporName = null;

        // HAPUS baris $this->validateOnly('pelapor_id') di sini
        // agar tidak memicu error saat user baru mulai mengetik.

        if (strlen($this->searchPelapor) > 1) {
            $this->pelapors = User::where('name', 'like', '%' . $this->searchPelapor . '%')
                ->orderBy('name')
                ->limit(10) // Batasi agar respons cepat
                ->get();

            // 2. Tampilkan dropdown hanya jika ada hasil
            $this->showPelaporDropdown = !empty($this->pelapors);
        } else {
            $this->pelapors = [];
            $this->showPelaporDropdown = false;
        }
    }

    /**
     * Menangani pemilihan pelapor dari dropdown hasil pencarian.
     */
    public function selectPelapor($id, $name)
    {
        if (property_exists($this, 'pelapor_id')) {
            $this->pelapor_id = $id;
        }
        $this->searchPelapor = $name;
        $this->showPelaporDropdown = false;
        $this->manualPelaporMode = false;

        // Picu validasi jika diperlukan
        if (method_exists($this, 'validateOnly')) {
            $this->validateOnly('pelapor_id');
        }
    }

    /**
     * Mengaktifkan mode manual jika nama tidak ditemukan di database.
     */
    public function enableManualPelapor()
    {
        $this->manualPelaporMode = true;
        $this->manualPelaporName = $this->searchPelapor;
        $this->showPelaporDropdown = false;

        if (property_exists($this, 'pelapor_id')) {
            $this->pelapor_id = null;
        }

        $this->dispatch('alert', [
            'text' => "Nama '" . $this->manualPelaporName . "' ditambahkan secara manual!",
            'duration' => 5000,
            'backgroundColor' => "background: linear-gradient(135deg, #00c853, #00bfa5);",
        ]);
    }

    /**
     * Memastikan pelapor_id tetap null jika user mengedit nama manual.
     */
    public function updatedManualPelaporName($value)
    {
        if (property_exists($this, 'pelapor_id')) {
            $this->pelapor_id = null;
        }
    }
}
