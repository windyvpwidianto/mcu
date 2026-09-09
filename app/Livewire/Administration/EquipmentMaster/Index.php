<?php

namespace App\Livewire\Administration\EquipmentMaster;

use Livewire\Component;
use App\Models\Location;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\EquipmentMaster;
use App\Models\InspectionChecklist;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EquipmentMasterImport;

class Index extends Component
{
    use WithPagination, WithFileUploads;
    public $file_excel;
    public $type, $specific_location, $is_active = true;
    public $technical_data = []; // Untuk menyimpan key-value dinamis (FE No, Capacity, dll)
    public $newKey, $newValue; // Input sementara untuk menambah baris JSON
    public $selected_id, $search;
    public $area;
    public $isEdit = false;
    public $search_area = '';
    // Pilih lokasi
    public $locations = [];
    public $show_location = false;
    public $searchLocation = '';
    public $location_id;
    // cari lokasi
    public $cari_locations = [];
    public $cari_show_location = false;
    public $cari_searchLocation = '';
    public $cari_location_id;

    public $previewData = []; // Untuk menampung data sementara
    public $showPreview = false;

    protected $rules = [
        'type' => 'required',
        'location_id' => 'required|exists:locations,id',
        'technical_data' => 'nullable|array|min:1',
    ];
    public function updatedType($value)
    {
        $this->generateTechnicalFields();
    }
    // Menambah baris spesifikasi baru (misal: "FE No" -> "PH001")
    public function addTechnicalField()
    {
        if ($this->newKey && $this->newValue) {
            $this->technical_data[$this->newKey] = $this->newValue;
            $this->reset(['newKey', 'newValue']);
        }
    }

    public function removeTechnicalField($key)
    {
        unset($this->technical_data[$key]);
    }

    public function updatedSearchLocation()
    {
        if (strlen($this->searchLocation) > 2) {
            $this->locations = Location::where('name', 'like', '%' . $this->searchLocation . '%')
                ->orderBy('name')->limit(10)->get();
            $this->show_location = true;
        } else {
            $this->show_location = false;
        }
        $this->reset('location_id');
    }

    public function selectLocation($id, $name)
    {
        $this->location_id = $id;
        $this->searchLocation = $name;
        $this->area = $name;
        $this->show_location = false;
        $this->generateTechnicalFields();
    }
    public function generateTechnicalFields()
    {
        if (!$this->type) return;

        if ($this->isEdit && !empty($this->technical_data)) return;

        $checklist = InspectionChecklist::where('equipment_type', $this->type)
            ->where('location_keyword', $this->area)
            ->first() ?:
            InspectionChecklist::where('equipment_type', $this->type)
            ->where('location_keyword', 'Default')
            ->first();

        if ($checklist && is_array($checklist->inputs)) {
            $fields = [];
            foreach ($checklist->inputs as $inputItem) {
                // Cek apakah $inputItem itu array (format baru) atau string (format lama)
                $labelText = is_array($inputItem) ? ($inputItem['label'] ?? '') : $inputItem;

                // GANTI SPASI DENGAN UNDERSCORE agar tidak pecah saat diketik
                $safeKey = str_replace(' ', '_', trim($labelText));

                if ($safeKey !== '') {
                    $fields[$safeKey] = '';
                }
            }
            $this->technical_data = $fields;
        }
    }
    public function updatedCariSearchLocation()
    {
        if (strlen($this->cari_searchLocation) > 2) {
            $this->cari_locations = Location::where('name', 'like', '%' . $this->cari_searchLocation . '%')
                ->orderBy('name')->limit(10)->get();
            $this->cari_show_location = true;
        } else {
            $this->cari_show_location = false;
        };
        $this->reset(['cari_location_id', 'search_area']);
    }


    public function selectCariLocation($id, $name)
    {
        $this->search_area = $name;
        $this->cari_location_id = $id;
        $this->cari_searchLocation = $name;
        $this->cari_show_location = false;
    }

    public function previewExcel()
    {
        $this->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:10240',
            'type' => 'required',
            'location_id' => 'required',
        ]);

        try {
            // 1. Definisikan urutan yang sama persis dengan Tab di Excel Anda
            $available_types = [
                'Fire Extinguisher',
                'Fire Hydrant',
                'Fire Hose Reel',
                'Fire sprinkler system',
                'Ring Buoy',
                'Eyewash & Safety Shower',
                'Muster Point',
                'Alarm',
                'First Aid Box',
                'Rescue Car',
                'Fire & Rescue Equipment'
            ];

            // 2. Cari tahu $this->type itu ada di urutan ke berapa (index)
            // Kita gunakan htmlspecialchars_decode untuk menangani karakter '&' dari browser
            $targetType = htmlspecialchars_decode($this->type);
            $index = array_search($targetType, $available_types);

            if ($index === false) {
                throw new \Exception("Tipe alat '{$targetType}' tidak terdaftar dalam sistem.");
            }

            // 3. Panggil Import. Laravel Excel akan mengembalikan array dengan index angka
            $importArray = Excel::toArray(
                new EquipmentMasterImport($targetType, $this->location_id),
                $this->file_excel
            );

            // 4. Ambil data berdasarkan index yang ditemukan
            // Jika di sistem index ke-5, maka ambil $importArray[5]
            $this->previewData = $importArray[$index] ?? [];

            if (empty($this->previewData)) {
                throw new \Exception("Sheet urutan ke-{$index} (Kategori: {$targetType}) tidak ditemukan atau kosong.");
            }

            $this->showPreview = true;
        } catch (\Exception $e) {
            $this->dispatch('alert', ['text' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function importExcel()
    {
        // Method ini sekarang dipanggil setelah user melihat preview
        try {
            // Karena data sudah divalidasi di preview, langsung jalankan import utama
            // Gunakan ->onlySheets($this->type) agar class import hanya memproses sheet tersebut
            Excel::import(
                new EquipmentMasterImport(htmlspecialchars_decode($this->type), $this->location_id),
                $this->file_excel->getRealPath()
            );

            $this->dispatch('alert', ['text' => 'Data Excel Berhasil Diimport!']);
            $this->reset(['previewData', 'showPreview']);
        } catch (\Exception $e) {
            $this->dispatch('alert', ['text' => 'Gagal Simpan: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    public function save()
    {
        $this->validate();

        // 1. Siapkan array baru untuk menampung data yang sudah dibersihkan
        $cleanTechnicalData = [];

        // 2. Loop data teknis dan ubah kembali underscore menjadi spasi pada KEY-nya
        foreach ($this->technical_data as $key => $value) {
            $originalKey = str_replace('_', ' ', $key);
            $cleanTechnicalData[$originalKey] = $value;
        }

        // 3. Simpan ke database menggunakan array yang sudah bersih
        EquipmentMaster::updateOrCreate(['id' => $this->selected_id], [
            'type' => $this->type,
            'location_id' => $this->location_id,
            'specific_location' => $this->specific_location,
            'technical_data' => $cleanTechnicalData, // Gunakan hasil pembersihan
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('alert', ['text' => $this->isEdit ? 'Data diperbarui!' : 'Data ditambah!']);
        $this->resetForm();
    }

    public function edit($id)
    {
        $data = EquipmentMaster::whereId($id)->first();
        $this->selected_id = $id;
        $this->type = $data->type;
        $this->location_id = $data->location_id;
        $this->specific_location = $data->specific_location;
        $this->searchLocation = $data->location->name;
        $this->isEdit = true;

        // Pastikan technical_data adalah array
        $raw_data = is_array($data->technical_data) ? $data->technical_data : [];

        // Ubah KEY dari spasi ke underscore agar Form bisa mengenali datanya
        $this->technical_data = [];
        foreach ($raw_data as $key => $value) {
            $safeKey = str_replace(' ', '_', $key);
            $this->technical_data[$safeKey] = $value;
        }
    }

    public function delete($id)
    {
        EquipmentMaster::destroy($id);
        $this->dispatch('alert', ['text' => 'Data berhasil dihapus!']);
    }

    public function resetForm()
    {
        $this->reset(['type', 'technical_data', 'selected_id', 'isEdit']);
    }
    public function render()
    {
        return view('livewire.administration.equipment-master.index', [
            'equipments' => EquipmentMaster::with('location')
                ->search($this->search)->byArea($this->search_area)
                ->paginate(10),
            'locations' => Location::all(),
            'available_types' => InspectionChecklist::distinct()->pluck('equipment_type'),
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
