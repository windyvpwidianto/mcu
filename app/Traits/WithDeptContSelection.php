<?php

namespace App\Traits;

use App\Models\Department;
use App\Models\Contractor;
use App\Models\ErmAssignment;

trait WithDeptContSelection
{
    // Definisikan properti yang dibutuhkan

    public $search = '';
    public $searchContractor = '';
    public $departments = [];
    public $contractors = [];
    public $showDropdown = false;
    public $showContractorDropdown = false;

    // Masukkan fungsi-fungsi Anda di bawah sini
    public function updatedSearch()
    {
        if (strlen($this->search) < 1) {
            $this->department_id = null;
            // Picu validasi keduanya agar error 'required_without' sinkron
            $this->validateOnly('department_id');
            $this->validateOnly('contractor_id');
        } elseif (strlen($this->search) > 1) {
            $this->departments = Department::where('department_name', 'like', '%' . $this->search . '%')
                ->orderBy('department_name')
                ->limit(80)
                ->get();
            $this->showDropdown = true;
        } else {
            $this->departments = [];
            $this->showDropdown = false;
        }
    }

    public function selectDepartment($id, $name)
    {
        // 1. Reset field kontraktor
        $this->reset('searchContractor', 'contractor_id');

        // 2. Set data departemen
        $this->department_id = $id;
        $this->search = $name;
        $this->showDropdown = false;

        // 3. Validasi keduanya (agar error di contractor_id hilang karena dept_id sudah terisi)
        $this->validateOnly('department_id');
        $this->validateOnly('contractor_id');

        // 4. Ambil penanggung jawab dengan struktur yang bersih
        $this->penanggungJawabOptions = ErmAssignment::where('department_id', $id)
            ->with('user:id,name')
            ->get()
            ->pluck('user')
            ->filter()
            ->values() // Reset index array
            ->toArray();
    }

    public function updatedSearchContractor()
    {
        if (strlen($this->searchContractor) < 1) {
            $this->contractor_id = null;
            $this->validateOnly('contractor_id');
            $this->validateOnly('department_id');
        } elseif (strlen($this->searchContractor) > 1) {
            $this->contractors = Contractor::where('contractor_name', 'like', '%' . $this->searchContractor . '%')
                ->orderBy('contractor_name')
                ->limit(80)
                ->get();
            $this->showContractorDropdown = true;
        } else {
            $this->contractors = [];
            $this->showContractorDropdown = false;
        }
    }

    public function selectContractor($id, $name)
    {
        $this->reset('search', 'department_id');
        $this->contractor_id = $id;
        $this->searchContractor = $name;
        $this->showContractorDropdown = false;

        $this->validateOnly('contractor_id');
        $this->validateOnly('department_id');

        $this->penanggungJawabOptions = ErmAssignment::where('contractor_id', $id)
            ->with('user:id,name')
            ->get()
            ->pluck('user')
            ->filter()
            ->values()
            ->toArray();
    }
    // Tambahkan di dalam file WithDeptContSelection.php

    public function loadInitialPenanggungJawab($type, $id)
    {
        if (!$id) return;

        $query = ErmAssignment::query()->with('user:id,name');

        if ($type === 'department') {
            $query->where('department_id', $id);
        } else {
            $query->where('contractor_id', $id);
        }

        $this->penanggungJawabOptions = $query->get()
            ->pluck('user')
            ->filter()
            ->values()
            ->toArray();
    }
    // ... dan seterusnya
}
