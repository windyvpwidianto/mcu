<?php

namespace App\Livewire\Inspection;

use App\Helpers\FileHelper;
use App\Models\FireProtection;
use App\Models\InspectionChecklist;
use App\Models\InspectionSession;
use App\Models\Location;
use App\Models\User;
use App\Traits\HasFireInspectionFields;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class FireInspectionEdit extends Component
{
    use WithFileUploads, HasFireInspectionFields;
    public $inspectionId;
    public $type, $location, $inspection_date, $remarks, $area, $location_id, $equipment_master_id;
    public $conditions = [];
    public $inspected_users = [];
    public $dokumentasi; // File baru yang diupload
    public $old_documentation; // Path file lama dari DB
    public $area_photo_path;
    public $new_area_photo_path;
    public $inspection_session;

    // Property untuk Searchable Dropdowns
    public $searchLocation = '';
    public $locations = [];
    public $show_location = false;
    public $searchResponsibility = '';
    public $pelapors = [];
    public $showPelaporDropdown = false;

    public $manualPelaporMode = false;
    public $manualPelaporName = '';
    public $responsible_id;
    public $technical_data = [];
    public $foto_area;
    public function mount($id)
    {
        $inspection = FireProtection::findOrFail($id);
        $this->inspectionId = $id;
        $this->inspection_session = $inspection->inspectionSession->id;
        $this->equipment_master_id = $inspection->equipment_master_id;
        $this->type = $inspection->equipmentMaster->type;
        $this->location = $inspection->equipmentMaster->specific_location;
        $this->area = $inspection->equipmentMaster->location->name;
        $this->searchLocation = $this->area;
        $this->inspection_date = $inspection->inspectionSession->inspection_date;
        $this->remarks = $inspection->remarks;
        // 🔥 INI KUNCINYA
        $this->technical_data = $inspection->equipmentMaster->technical_data ?? [];

        // Checklist hasil inspeksi
        $this->conditions = $inspection->conditions ?? [];
        $this->old_documentation = $inspection->documentation_path;

        $this->area_photo_path = $inspection->inspectionSession->area_photo_path;

        // Load Inspected Users
        if ($inspection->inspected_by) {
            $names = explode('|', $inspection->inspected_by);
            foreach ($names as $name) {
                $this->inspected_users[] = ['id' => null, 'name' => $name];
            }
        }
        $this->adjustFieldsByLocation();
    }

    public function adjustFieldsByLocation()
    {
        if (
            isset($this->fields['Fire Hydrant']) &&
            str_contains(strtolower($this->searchLocation), 'maesa camp')
        ) {
            $this->fields['Fire Hydrant']['checks'] = [
                'Box',
                'Hose',
                'Rack',
                'Valve',
                'Nozel',
            ];
        }
    }


    public function rules()
    {
        return [
            'location'        => 'required|string|max:255',
            'inspection_date' => 'required|date',
            'inspected_users' => 'required|array|min:1',
            'conditions'      => 'required|array',
            'type'            => 'required|string',
            'area'            => 'required',
            'dokumentasi'     => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ];
    }
    public function clearNewFotoArea()
    {
        $this->foto_area = null;
    }
    // 🔥 FUNGSI HAPUS FOTO AREA
    public function removeAreaPhoto()
    {
        if ($this->area_photo_path) {
            // 1. Hapus file fisik dari storage
            if (Storage::disk('public')->exists($this->area_photo_path)) {
                Storage::disk('public')->delete($this->area_photo_path);
            }

            // 2. Update SEMUA record yang memiliki foto area yang sama (denormalized)
            // agar semua baris di area tersebut fotonya ikut terhapus
            FireProtection::where('area_photo_path', $this->area_photo_path)
                ->update(['area_photo_path' => null]);

            // 3. Reset state di UI
            $this->area_photo_path = null;

            $this->dispatch('notify', ['type' => 'success', 'message' => 'Foto area berhasil dihapus']);
        }
    }
    public function clearNewUpload()
    {
        $this->dokumentasi = null;
    }

    // 2. Menghapus file lama yang sudah ada di server/database
    public function deleteOldFile()
    {
        if ($this->old_documentation) {
            // 1. Hapus file fisik dari storage
            if (Storage::disk('public')->exists($this->old_documentation)) {
                Storage::disk('public')->delete($this->old_documentation);
            }

            // 2. UPDATE DATABASE (Ini yang kurang)
            FireProtection::find($this->inspectionId)->update([
                'documentation_path' => null
            ]);

            // 3. Kosongkan variabel state agar tampilan di UI langsung hilang
            $this->old_documentation = null;

            $this->dispatch('notify', ['type' => 'success', 'message' => 'File berhasil dihapus dari database']);
        }
    }
    public function updatedFotoArea()
    {
        $this->validate(['foto_area' => 'image|max:10240']);

        if ($this->area_photo_path) {
            FileHelper::deleteFile($this->area_photo_path);
        }

        $result = FileHelper::compressAndStore($this->foto_area, 'inspections/area-photos');

        $this->area_photo_path = $result;
    }
    public function update()
    {
        $this->validate();

        $data = [

            'inspected_by' => implode('|', array_column($this->inspected_users, 'name')),
            'conditions' => $this->conditions,
            'remarks' => $this->remarks,
        ];

        if ($this->dokumentasi) {
            // Hapus file lama jika ada
            if ($this->old_documentation) {
                Storage::disk('public')->delete($this->old_documentation);
            }
            $data['documentation_path'] = FileHelper::compressAndStore($this->dokumentasi, 'inspections/documents');
        }

        FireProtection::find($this->inspectionId)->update($data);
        InspectionSession::whereId($this->inspection_session)
            ->update([
                'inspection_date' => $this->inspection_date,
                'area_photo_path' =>  $this->area_photo_path
            ]);

        session()->flash('success', 'Data berhasil diperbarui!');
        return $this->redirect(route('fire-inspection-list'), navigate: true);
    }

    public function updatedSearchResponsibility()
    {
        $this->reset('manualPelaporName');
        $this->manualPelaporMode = false;
        if (strlen($this->searchResponsibility) > 1) {
            $this->pelapors = User::where('name', 'like', '%' . $this->searchResponsibility . '%')
                ->orderBy('name')
                ->limit(50)
                ->get();
            $this->showPelaporDropdown = true;
        } else {
            $this->pelapors = [];
            $this->showPelaporDropdown = false;
        }
    }
    public function selectPelapor($id, $name)
    {
        // $this->searchResponsibility = $name;
        $this->showPelaporDropdown = false;
        $this->manualPelaporMode = false;

        if (!collect($this->inspected_users)->contains('id', $id)) {
            $this->inspected_users[] = [
                'id' => $id,
                'name' => $name
            ];
        }
        $this->reset(['searchResponsibility', 'showPelaporDropdown']);
    }
    public function enableManualPelapor()
    {
        $this->manualPelaporMode = true;
        $this->manualPelaporName = $this->searchResponsibility; // isi default sama dengan isi search
        // Masukkan data manual (id null)
        $this->inspected_users[] = [
            'id' => null,
            'name' => $this->manualPelaporName
        ];
        $this->reset(['manualPelaporName', 'searchResponsibility', 'showPelaporDropdown', 'manualPelaporMode']);
    }
    public function updatedManualPelaporName($value)
    {
        $this->responsible_id = null;
    }
    public function removeInspectedUser($index)
    {
        unset($this->inspected_users[$index]);
        $this->inspected_users = array_values($this->inspected_users); // re-index array
    }

    public function addPelaporManual()
    {
        $this->searchResponsibility = $this->manualPelaporName;
        $this->showPelaporDropdown = false;
        $this->responsible_id = null;
    }

    public function updatedSearchLocation()
    {
        if (strlen($this->searchLocation) > 2) {
            $this->locations = Location::where('name', 'like', '%' . $this->searchLocation . '%')
                ->orderBy('name')
                ->limit(50)
                ->get();
            $this->show_location = true;
        } else {
            $this->locations = [];
            $this->show_location = false;
        }
    }
    public function selectLocation($id, $name)
    {
        $this->location_id = $id;
        $this->searchLocation = $name;
        $this->area = $name;
        $this->show_location = false;
        $this->validateOnly('location_id');
    }

    public function render()
    {
        return view('livewire.inspection.fire-inspection-edit', [
            'availableTypes' => InspectionChecklist::distinct()->pluck('equipment_type'),
        ]);
    }
}
