<?php

namespace App\Livewire\Administration\People;

use App\Models\Compliance as ModelsCompliance;
use App\Models\ComplianceMaster;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class Compliance extends Component
{
    public $userId;
    public $compliance_class;
    public $compliance_name;
    public $start_date;
    public $isEditMode = false;
    public $complianceId;

    public function mount($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
    }

    /**
     * Logika untuk menentukan warna dan label badge expired
     * Bisa dipanggil di blade dengan $this->getExpiryStatus($item->expired_at)
     */
    public function getExpiryStatus($expired_at)
    {
        $today = Carbon::now()->startOfDay();
        $expiryDate = $expired_at ? Carbon::parse($expired_at)->startOfDay() : null;

        // 1. Jika NULL (Lifetime) -> Hijau
        if (!$expiryDate) {
            return [
                'class' => 'badge-soft badge-success text-white',
                'label' => 'Lifetime/Permanen'
            ];
        }

        // 2. Jika sudah lewat tanggal hari ini -> Merah
        if ($expiryDate->lessThan($today)) {
            return [
                'class' => 'badge-soft badge-error  font-bold',
                'label' => $expiryDate->format('d-m-Y') . ' (Expired)'
            ];
        }

        // 3. Jika selisih hari <= 30 hari ke depan -> Kuning
        // diffInDays secara default menghasilkan nilai absolut
        if ($today->diffInDays($expiryDate, false) <= 30) {
            return [
                'class' => 'badge-soft badge-warning ',
                'label' => $expiryDate->format('d-m-Y') . ' (Soon)'
            ];
        }

        // 4. Jika masih lama (> 30 hari) -> Hijau
        return [
            'class' => 'badge-soft badge-success ',
            'label' => $expiryDate->format('d-m-Y')
        ];
    }

    public function getExistingClassesProperty()
    {
        return ComplianceMaster::select('class')
            ->distinct()
            ->whereNotNull('class')
            ->orderBy('class', 'asc')
            ->pluck('class');
    }

    public function edit(ModelsCompliance $data)
    {
        $this->isEditMode = true;
        $this->complianceId = $data->id;
        $data->load('master');

        $this->fill([
            'compliance_class' => $data->master->class,
            'compliance_name' => $data->master->name,
            'start_date' => Carbon::parse($data->start_date)->format('d-m-Y'),
        ]);

        $this->dispatch('open-modal-compliance');
    }

    public function openCreateModal()
    {
        $this->reset(['complianceId', 'compliance_name', 'compliance_class', 'start_date']);
        $this->isEditMode = false;
        $this->dispatch('open-modal-compliance');
    }

    public function save()
    {
        $this->validate([
            'compliance_name' => 'required',
            'start_date' => 'required',
        ]);

        $master = ComplianceMaster::where('name', $this->compliance_name)->first();
        $startDate = Carbon::createFromFormat('d-m-Y', trim($this->start_date));

        $expiredAt = ($master->duration_months > 0)
            ? $startDate->copy()->addMonths($master->duration_months)
            : null;

        $payload = [
            'user_id' => $this->userId,
            'compliance_master_id' => $master->id,
            'start_date' => $startDate->format('Y-m-d'),
            'expired_at' => $expiredAt ? $expiredAt->format('Y-m-d') : null,
            'status' => true,
        ];

        if ($this->isEditMode) {
            ModelsCompliance::find($this->complianceId)->update($payload);
        } else {
            ModelsCompliance::create($payload);
        }

        $this->dispatch('close-modal-compliance');
        $this->dispatch('alert', 'Data berhasil ' . ($this->isEditMode ? 'diperbarui' : 'disimpan'));
    }

    public function closed()
    {
        $this->reset(['complianceId', 'compliance_name', 'compliance_class', 'start_date']);
        $this->dispatch('close-modal-compliance');
    }

    public function render()
    {
        $names = ComplianceMaster::select('name')->distinct()
            ->when($this->compliance_class, fn($q) => $q->where('class', $this->compliance_class))
            ->whereNotNull('name')
            ->orderBy('name', 'asc')
            ->pluck('name');

        return view('livewire.administration.people.compliance', [
            'compliances' => ModelsCompliance::where('user_id', $this->userId)
                ->with('master')
                ->latest()
                ->get(),
            'compliance_names' => $names
        ]);
    }
}
