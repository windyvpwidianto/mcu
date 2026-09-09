<?php

namespace App\Traits;

use App\Models\Location;


trait WithSearchLocation
{
    public $locations = [];
    public $searchLocation = '';
    public $show_location = false;
    // Search Location
    public function updatedSearchLocation()
    {
        if (strlen($this->searchLocation) < 1) {
            $this->location_id = null;
            // Picu validasi keduanya agar error 'required_without' sinkron
            $this->validateOnly('location_id');
        } elseif (strlen($this->searchLocation) > 2) {
            $this->locations = Location::where('name', 'like', '%' . $this->searchLocation . '%')
                ->orderBy('name')
                ->limit(80)
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
        $this->show_location = false;
        $this->validateOnly('location_id');
    }
}
