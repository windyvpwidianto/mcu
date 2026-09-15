<?php

namespace App\Livewire\Mcu;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Department;
use App\Models\Contractor;
use Carbon\Carbon;

class McuUpcoming extends Component
{
    use WithPagination;

    public $search = '';
    public $filterPeriod = 'all'; // all, next_30, next_7, overdue
    public $filterDepartment = '';
    public $filterContractor = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterPeriod()
    {
        $this->resetPage();
    }

    public function render()
    {
        $today = Carbon::today();

        $query = User::whereNotNull('next_mcu_date')
            ->with(['departments', 'contractors'])
            ->when($this->search, function($q) {
                $q->where(function($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('employee_id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterDepartment, function($q) {
                $q->whereHas('departments', function($q2) {
                    $q2->where('departments.id', $this->filterDepartment);
                });
            })
            ->when($this->filterContractor, function($q) {
                $q->whereHas('contractors', function($q2) {
                    $q2->where('contractors.id', $this->filterContractor);
                });
            });

        // Filter based on period
        if ($this->filterPeriod === 'next_30') {
            $query->whereBetween('next_mcu_date', [$today->copy()->addDays(1)->toDateString(), $today->copy()->addDays(30)->toDateString()]);
        } elseif ($this->filterPeriod === 'next_7') {
            $query->whereBetween('next_mcu_date', [$today->copy()->addDays(1)->toDateString(), $today->copy()->addDays(7)->toDateString()]);
        } elseif ($this->filterPeriod === 'overdue') {
            $query->where('next_mcu_date', '<', $today->toDateString());
        } elseif ($this->filterPeriod === 'today') {
            $query->where('next_mcu_date', '=', $today->toDateString());
        }

        $query->orderBy('next_mcu_date', 'asc');

        // Statistics
        $statAll = User::whereNotNull('next_mcu_date')->count();
        $statOverdue = User::whereNotNull('next_mcu_date')->where('next_mcu_date', '<', $today->toDateString())->count();
        $statNext30 = User::whereNotNull('next_mcu_date')->whereBetween('next_mcu_date', [$today->copy()->addDays(1)->toDateString(), $today->copy()->addDays(30)->toDateString()])->count();
        $statNext7 = User::whereNotNull('next_mcu_date')->whereBetween('next_mcu_date', [$today->copy()->addDays(1)->toDateString(), $today->copy()->addDays(7)->toDateString()])->count();

        return view('livewire.mcu.mcu-upcoming', [
            'users' => $query->paginate(15),
            'departments' => Department::orderBy('department_name')->get(),
            'contractors' => Contractor::orderBy('contractor_name')->get(),
            'stats' => [
                'all' => $statAll,
                'overdue' => $statOverdue,
                'next_30' => $statNext30,
                'next_7' => $statNext7,
            ]
        ]);
    }
}
