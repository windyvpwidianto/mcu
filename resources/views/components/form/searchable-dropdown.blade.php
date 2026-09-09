@props([
'label' => null,
'placeholder' => 'Cari...',
'modelsearch' => null, // Menampung 'searchLocation'
'modelid' => null, // Menampung 'location_id' untuk error highlight
'options' => [], // Data array/collection hasil search
'showdropdown' => false, // Boolean untuk kontrol visibility dropdown
'required' => false,
'disabled' => false,
'clickaction' => 'selectLocation',
'namedb' => 'name',
])

<fieldset class="fieldset">
    @if ($label)
    <x-form.label :label="$label" :required="$required" />
    @endif

    {{-- 1. Gunakan x-data="{ open: false }" untuk kontrol UI lokal --}}
    <div class="relative" x-data="{ open: false }">
        <input
            {{ $disabled ? 'disabled' : '' }}
            type="text"
            wire:model.live.debounce.300ms="{{ $modelsearch }}"
            placeholder="{{ $placeholder }}"
            {{-- 2. Trigger buka/tutup via Alpine --}}
            x-on:focus="open = true"
            x-on:click.outside="open = false"
            x-on:keydown.escape="open = false"
            {{ $attributes->merge([
                'class' =>
                    'input input-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs ' .
                    ($errors->has($modelid) ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : ''),
            ]) }} />

        {{-- 3. Tampilkan dropdown hanya jika showdropdown true DAN open true --}}
        @if ($showdropdown && count($options) > 0)
        <ul
            x-show="open"
            class="absolute z-[9999] w-full mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60"
            style="display: none;" {{-- Mencegah flickering saat load pertama --}}>
            {{-- Spinner Loading saat aksi klik berjalan --}}
            <div wire:loading wire:target="{{ $clickaction }}" class="flex flex-col items-center justify-center p-4 space-y-2 text-center">
                <span class="loading loading-spinner loading-sm text-secondary"></span>
            </div>

            {{-- List Opsi --}}
            <div wire:loading.remove wire:target="{{ $clickaction }}">
                @foreach ($options as $opt)
                <li
                    wire:click="{{ $clickaction }}({{ $opt->id }}, '{{ addslashes($opt->{$namedb}) }}')"
                    wire:key="opt-{{ $opt->id }}"
                    {{-- 4. Tutup dropdown saat item dipilih --}}
                    x-on:click="open = false"
                    class="px-3 py-2 text-sm cursor-pointer hover:bg-base-200 list-none">
                    {{ $opt->{$namedb} }}
                </li>
                @endforeach
            </div>
        </ul>
        @endif
    </div>

    @if ($modelid)
    <x-label-error :messages="$errors->get($modelid)" />
    @endif
</fieldset>