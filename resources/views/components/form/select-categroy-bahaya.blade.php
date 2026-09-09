@props([
'keyWord' => null,
'required' => false,
'ktas' => [],
'ttas' => [],
'options_label' => '-- Pilih Kategori Bahaya --',
'model_kta' => null,
'model_tta' => null,
])

<fieldset {{ $attributes->merge(['class' => 'fieldset']) }}>
    <div class="flex items-center gap-4 -mb-0.5">
        {{-- Radio Kondisi Tidak Aman (KTA) --}}
        <div class="flex items-center gap-1">
            <input id="kta" value="kta" wire:model.live="keyWord"
                class="peer/kta radio radio-xs radio-accent" type="radio" name="keyWord" />

            <x-form.label for="kta" class="peer-checked/kta:text-accent text-[10px] cursor-pointer"
                label="Kondisi Tidak Aman"
                :required="$keyWord === 'kta' && $required" />
        </div>

        {{-- Radio Tindakan Tidak Aman (TTA) --}}
        <div class="flex items-center gap-1">
            <input id="tta" value="tta" wire:model.live="keyWord"
                class="peer/tta radio radio-xs radio-primary" type="radio" name="keyWord" />

            <x-form.label for="tta" class="peer-checked/tta:text-primary text-[10px] cursor-pointer"
                label="Tindakan Tidak Aman"
                :required="$keyWord === 'tta' && $required" />
        </div>
    </div>
    {{-- Dropdown KTA --}}
    <div wire:key="dropdown-kta-{{ $model_kta }}" class="{{ $keyWord === 'kta' ? 'block' : 'hidden' }} ">
        <x-form.select
            :model="$model_kta"
            :options="$ktas"
            optionValue="id"
            optionLabel="name"
            :placeholder="$options_label" />
    </div>

    {{-- Dropdown TTA --}}
    <div wire:key="dropdown-tta-{{ $model_tta }}" class="{{ $keyWord === 'tta' ? 'block' : 'hidden' }} ">
        <x-form.select
            :model="$model_tta"
            :options="$ttas"
            optionValue="id"
            optionLabel="name"
            :placeholder="$options_label"
            </div>
</fieldset>