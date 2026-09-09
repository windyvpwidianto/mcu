<?php

namespace App\Livewire\Administration\PartOfBody;

use App\Models\BodyPart;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Properti Form
    public $body_part_id;
    public $name, $name_en, $category, $code;

    public $search = '';
    public $filterCategory = ''; // Tambahkan ini untuk filter kategori
    public $isEditing = false;

    protected $rules = [
        'name' => 'required|min:3',
        'name_en' => 'nullable|min:3',
        'category' => 'required',
        'code' => 'required|unique:body_parts,code',
    ];
    public function render()
    {
        return view('livewire.administration.part-of-body.index', [
            'bodyParts' => BodyPart::query()
                ->searchName($this->search)          // Memanggil scopeSearchName
                ->searchCategory($this->filterCategory) // Memanggil scopeSearchCategory
                ->latest()
                ->paginate(10)
        ]);
    }
    public function getExistingCategoriesProperty()
    {
        return BodyPart::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category', 'asc')
            ->pluck('category');
    }
    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['name', 'name_en', 'category', 'code', 'body_part_id', 'isEditing']);
        // Trigger Javascript untuk buka modal DaisyUI v5
        $this->dispatch('open-body-modal');
    }

    public function store()
    {
        $this->validate();

        BodyPart::create([
            'name' => $this->name,
            'name_en' => $this->name_en,
            'category' => $this->category,
            'code' => Str::slug($this->code, '_'),
        ]);

        $this->dispatch('close-body-modal');
        session()->flash('success', 'Bagian tubuh berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $this->isEditing = true;
        $this->body_part_id = $id;
        $part = BodyPart::findOrFail($id);

        $this->name = $part->name;
        $this->name_en = $part->name_en;
        $this->category = $part->category;
        $this->code = $part->code;

        $this->dispatch('open-body-modal');
    }

    public function update()
    {
        $this->validate([
            'name' => 'required',
            'category' => 'required',
            'code' => 'required|unique:body_parts,code,' . $this->body_part_id,
        ]);

        $part = BodyPart::find($this->body_part_id);
        $part->update([
            'name' => $this->name,
            'name_en' => $this->name_en,
            'category' => $this->category,
            'code' => $this->code,
        ]);

        $this->dispatch('close-body-modal');
        session()->flash('success', 'Data berhasil diperbarui.');
    }

    public function delete($id)
    {
        BodyPart::destroy($id);
        session()->flash('success', 'Data berhasil dihapus.');
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatatingFilterCategory()
    {
        $this->resetPage();
    }
    public function paginationView()
    {
        return 'paginate.pagination';
    }
}
