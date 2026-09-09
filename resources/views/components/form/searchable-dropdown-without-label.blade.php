@props([
'placeholder' => 'Cari lokasi...',
'modelsearch' => null,
'modelid' => null,
'options' => [],
'showdropdown' => false,
'disabled' => false,
'clickaction' => 'selectLocation',
'namedb' => 'name'
])

{{-- 1. Menggunakan x-data="{ open: false }" untuk menghilangkan error .live --}}
<div class="relative mb-1" x-data="{ open: false }">

    <input
        x-ref="trigger"
        {{ $disabled ? 'disabled' : '' }}
        type="text"
        {{-- Pencarian tetap Live menggunakan wire:model.live --}}
        wire:model.live="{{ $modelsearch }}"
        placeholder="{{ __($placeholder) }}"

        {{-- Kontrol dropdown via Alpine --}}
        x-on:focus="open = true"
        x-on:keydown.escape="open = false"
        x-on:click.outside="open = false"

        {{ $attributes->merge([
            'class' => 'input input-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs ' .
            ($disabled ? 'bg-base-200 opacity-70 ' : '') .
            ($errors->has($modelid) ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '')
        ]) }} />

    @if ($showdropdown)
    <template x-teleport="body">
        <ul
            x-show="open"
            wire:ignore.self
            x-anchor.bottom-start.offset.4="$refs.trigger"
            {{-- Fungsi ini memastikan lebar dropdown sama dengan input --}}
            :style="{ width: $refs.trigger.offsetWidth + 'px' }"
            class="fixed z-[9999] overflow-auto border rounded-md shadow-xl bg-base-100 border-base-300 max-h-60">

            {{-- Spinner Loading --}}
            <div wire:loading wire:target="{{ $modelsearch }}" class="flex flex-col items-center justify-center p-4 space-y-2 text-center">
                <span class="loading loading-spinner loading-sm text-secondary"></span>
                <span class="text-[10px] italic text-gray-400">{{ __('Memuat data...') }}</span>
            </div>

            {{-- Render List Opsi --}}
            <div wire:loading.remove wire:target="{{ $modelsearch }}">
                @if(count($options) > 0)
                @foreach ($options as $opt)
                <li
                    wire:click="{{ $clickaction }}({{ $opt->id }}, '{{ addslashes($opt->{$namedb}) }}')"
                    wire:key="opt-location-{{ $opt->id }}"
                    {{-- Tutup dropdown setelah pilih item --}}
                    x-on:click="open = false"
                    class="px-3 py-2 text-sm list-none cursor-pointer hover:bg-base-200 text-base-content">
                    {{ $opt->{$namedb} }}
                </li>
                @endforeach
                @else
                <div class="px-3 py-2 text-xs italic text-gray-500">
                    {{ __('Lokasi tidak ditemukan') }}
                </div>
                @endif
            </div>
        </ul>
    </template>
    @endif
</div>

@if($modelid)
<x-label-error :messages="$errors->get($modelid)" />
@endif