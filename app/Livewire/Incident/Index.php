<?php

namespace App\Livewire\Incident;

use App\Models\Contractor;
use App\Models\Department;
use App\Models\EventSubType;
use App\Models\EventType;
use App\Models\IncidentReport;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public $search;
    public $filterEventType = [];
    public $filterEventSubType = [];
    public $filterDept = [];
    public $filterStatus = [];

    // Reset halaman jika filter berubah
    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function updatedFilterCategory()
    {
        $this->resetPage();
    }

    public function editIncident($id)
    {
        // Contoh: Redirect ke halaman form edit
        return redirect()->route('incident-detail', $id);
    }
    public function render()
    {
        // 1. Ambil data untuk opsi filter di header
        $filterOptions = [
            // Ambil Departemen yang hanya memiliki record di incident_reports
            'departments' => Department::whereHas('incidentReports')
                ->select('id', 'department_name')
                ->get()
                ->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->department_name,
                    'type' => 'dept'
                ]),

            // Ambil Kontraktor yang hanya memiliki record di incident_reports
            'contractors' => Contractor::whereHas('incidentReports')
                ->select('id', 'contractor_name')
                ->get()
                ->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->contractor_name,
                    'type' => 'cont'
                ]),

            // Filter Status sesuai urutan ENUM database
            'statuses' => ['Open', 'In Progress', 'Action Required', 'Closed'],

            // Gabungkan Tipe dan Jenis menggunakan Relasi
            'eventGroups' => EventType::onlyIncidents()
                // 1. Hanya ambil EventType yang dipakai di IncidentReport
                ->whereHas('incidentReports')
                ->with(['eventSubTypes' => function ($query) {
                    $query->onlyIncidents()
                        // 2. Hanya ambil EventSubType yang dipakai di IncidentReport
                        ->whereHas('incidentReports')
                        ->select('id', 'event_type_id', 'event_sub_type_name');
                }])
                ->select('id', 'event_type_name')
                ->get(),
        ];

        // Gabungkan depts dan contractors untuk list "Divisi" di view
        $filterOptions['allDivisions'] = $filterOptions['departments']->concat($filterOptions['contractors']);

        // 2. Query utama dengan filter
        $incidents = IncidentReport::query()
            // Eager Load relasi agar tidak lambat (N+1)
            ->with(['eventType', 'eventSubType', 'department', 'contractor', 'pic'])

            // Filter Pencarian (Nomor Referensi)
            ->when($this->search, function ($query) {
                $query->where('report_number', 'like', '%' . $this->search . '%');
            })

            // Filter Status (Multiple Selection)
            ->when($this->filterStatus, function ($query) {
                $query->whereIn('status', (array) $this->filterStatus);
            })

            // Filter Departemen / Kontraktor (Dua Relasi)
            ->when($this->filterDept, function ($query) {
                $query->where(function ($q) {
                    foreach ((array) $this->filterDept as $value) {
                        // Logic pemisahan ID berdasarkan prefix 'dept-' atau 'cont-'
                        if (str_contains($value, '-')) {
                            [$type, $id] = explode('-', $value);
                            if ($type === 'dept') $q->orWhere('department_id', $id);
                            if ($type === 'cont') $q->orWhere('contractor_id', $id);
                        } else {
                            // Fallback jika hanya mengirimkan ID murni
                            $q->orWhere('department_id', $value)->orWhere('contractor_id', $value);
                        }
                    }
                });
            })

            // Filter Tipe & Sub Tipe
            ->when($this->filterEventType, fn($q) => $q->whereIn('event_type_id', (array) $this->filterEventType))
            ->when($this->filterEventSubType, fn($q) => $q->whereIn('event_sub_type_id', (array) $this->filterEventSubType))

            ->latest('date_time')
            ->paginate(20);

        return view('livewire.incident.index', [
            'incidents'     => $incidents,
            'filterOptions' => $filterOptions
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
