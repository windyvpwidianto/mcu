<?php

namespace App\Livewire\Administration\Compliances;

use App\Models\ComplianceMaster;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public $name, $description, $class, $duration_months = 12, $status = 1;
    public $selected_id;
    public $class_search='';
    public $isEdit = false;
    protected function rules()
    {
        return [
            'name'            => 'required|string|min:3|max:255',
            'description'     => 'required|string|min:10',
            'class'           => 'required|string|max:100',
            'duration_months' => 'nullable|integer|min:0|max:120', // Maksimal 10 tahun (120 bulan)
            'status'          => 'required|boolean',
        ];
    }

    protected function messages()
    {
        return [
            'name.required'            => 'Nama kepatuhan wajib diisi.',
            'name.min'                 => 'Nama kepatuhan minimal 3 karakter.',
            'description.required'     => 'Deskripsi wajib diisi agar informasi jelas.',
            'description.min'          => 'Deskripsi terlalu singkat, berikan penjelasan lebih detail.',
            'class.required'           => 'Kategori (Class) wajib dipilih atau diisi.',
            'duration_months.integer'  => 'Durasi harus berupa angka bulan.',
            'duration_months.min'      => 'Durasi tidak boleh negatif. Isi 0 untuk Permanen.',
        ];
    }
    public function create()
    {
        $this->reset(['name', 'description', 'class', 'duration_months', 'selected_id']);
        $this->isEdit = false;
        $this->status = 1;

        // Kirim sinyal ke JS untuk buka modal
        $this->dispatch('open-compliance-modal');
    }
    public function edit($id)
    {
        $this->isEdit = true;
        $master = ComplianceMaster::findOrFail($id);

        $this->selected_id = $id;
        $this->name = $master->name;
        $this->description = $master->description;
        $this->class = $master->class;
        $this->duration_months = $master->duration_months ?? 0;
        $this->status = $master->status;

        // Kirim sinyal ke JS untuk buka modal
        $this->dispatch('open-compliance-modal');
    }

    public function save()
    {
        // Menjalankan validasi berdasarkan rules & messages di atas
        $this->validate();

        // Logika pembuatan Title (seperti diskusi sebelumnya)
        $generatedTitle = $this->duration_months > 0
            ? "{$this->name} (expiry in {$this->duration_months} bulan)"
            : "{$this->name} (Permanen)";

        ComplianceMaster::updateOrCreate(['id' => $this->selected_id], [
            'name'            => $this->name,
            'description'     => $this->description,
            'class'           => $this->class,
            'duration_months' => ($this->duration_months > 0) ? $this->duration_months : null,
            'title'           => $generatedTitle,
            'status'          => $this->status,
        ]);
        $text = $this->selected_id? 'Master data berhasil diupdate!':'Master data berhasil ditambahkan!';
        $this->dispatch('alert', ['text' => $text]);
        $this->dispatch('close-compliance-modal');
    }
    public function getExistingClassesProperty()
    {
        return ComplianceMaster::select('class')
            ->distinct()
            ->whereNotNull('class')
            ->orderBy('class', 'asc')
            ->pluck('class');
    }
    public function showDelete($id)
    {
        $this->selected_id = $id;
    }
    public function delete()
    {
        if ($this->selected_id) {
            ComplianceMaster::findOrFail($this->selected_id)->delete();

            // reset
            $this->selected_id = null;
           $this->dispatch('close-delete-modal');

            // opsional: emit event untuk notifikasi / refresh tabel
            $this->dispatch(
                'alert',
                [
                    'text' => "Data berhasil di hapus!!!",
                    'duration' => 5000,
                    'destination' => '/contact',
                    'newWindow' => true,
                    'close' => true,
                    'backgroundColor' => "linear-gradient(to right, #ff3333, #ff6666)",
                ]
            );
        }
    }
    public function render()
    {
        return view('livewire.administration.compliances.index', [
            'ComplianceMaster' => ComplianceMaster::searchClass($this->class_search)->paginate(20)
        ]);
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
