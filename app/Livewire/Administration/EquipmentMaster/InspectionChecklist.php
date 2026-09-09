<?php



namespace App\Livewire\Administration\EquipmentMaster;



use Livewire\Component;

use App\Models\Location;

use App\Models\InspectionChecklist as InspectionChecklistModel;



class InspectionChecklist extends Component

{

    public $checklists;

    public $location_id;

    public $checklist_id, $equipment_type, $location_keyword, $location_specific;

    public $searchLocation = '';

    public $locations = [];

    public $show_location = false;



    // Array dinamis untuk form

    public $inputs = [''];

    public $checks = [''];



    public function render()

    {

        $this->checklists = InspectionChecklistModel::all();

        return view('livewire.administration.equipment-master.inspection-checklist');
    }



    // Menambah baris input kosong

    public function addInput()

    {

        $this->inputs[] = '';
    }

    public function removeInput($index)

    {

        unset($this->inputs[$index]);

        $this->inputs = array_values($this->inputs);
    }



    // Menambah baris check kosong

    public function addCheck()
    {
        // Menambah item baru dengan default type 'checkbox'
        $this->checks[] = ['name' => '', 'type' => 'checkbox'];
    }

    public function removeCheck($index)

    {

        unset($this->checks[$index]);

        $this->checks = array_values($this->checks);
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

        $this->location_keyword = $name;

        $this->show_location = false;
    }

    public function save()

    {

        $this->validate([

            'equipment_type' => 'required',

            'location_keyword' => 'nullable',
            'location_specific' => 'nullable',

            'inputs.*' => 'required',

            'checks.*' => 'required',

        ]);



        InspectionChecklistModel::updateOrCreate(

            ['id' => $this->checklist_id],

            [

                'equipment_type' => $this->equipment_type,

                'location_keyword' => $this->location_keyword ?? 'Default',
                'location_specific' => $this->location_specific ?? 'Default',

                'inputs' => $this->inputs,

                'checks' => $this->checks,

            ]

        );



        session()->flash('message', $this->checklist_id ? 'Updated!' : 'Created!');

        $this->resetForm();

        $this->dispatch('close-checklist-modal');
    }



    public function edit($id)
    {
        $checklist = InspectionChecklistModel::find($id);
        $this->checklist_id = $id;
        $this->equipment_type = $checklist->equipment_type;
        $this->location_keyword = $checklist->location_keyword;
        $this->location_specific = $checklist->location_specific;
        $this->searchLocation = $checklist->location_keyword;
        $this->inputs = $checklist->inputs;

        // Logic Handle data lama agar tidak error saat diedit
        $rawChecks = $checklist->checks;
        $formattedChecks = [];
        foreach ($rawChecks as $check) {
            if (is_array($check)) {
                $formattedChecks[] = $check;
            } else {
                // Jika data lama masih string, otomatis anggap checkbox
                $formattedChecks[] = ['name' => $check, 'type' => 'checkbox'];
            }
        }
        $this->checks = $formattedChecks;
    }



    public function delete($id)

    {

        InspectionChecklistModel::find($id)->delete();
    }



    public function resetForm()
    {
        $this->reset(['checklist_id', 'equipment_type', 'location_keyword', 'location_specific', 'inputs', 'checks']);
        $this->inputs = [''];
        // Inisialisasi checks sebagai array of objects
        $this->checks = [['name' => '', 'type' => 'checkbox']];
    }
}
