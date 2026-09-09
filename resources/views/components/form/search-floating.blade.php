@props([
'label' => null,
'placeholder' => ' ',
'modelsearch' => null,
'modelid' => null,
'options' => [],
'showdropdown' => false,
'required' => false,
'disabled' => false,
'clickaction' => 'selectLocation',
'namedb' => 'name'
])

<fieldset class="fieldset">
    {{-- Inisialisasi x-data dengan entangle agar sinkron dengan Livewire --}}
    <div class="relative w-full" x-data="{ open: @entangle($attributes->wire('model').'.live') }">

        <label class="w-full floating-label">
            <input
                x-ref="trigger" {{-- Jangkar untuk x-anchor --}}
                {{ $disabled ? 'disabled' : '' }}
                type="text"
                wire:model.live.debounce.300ms="{{ $modelsearch }}"
                placeholder="{{ $placeholder }}"
                x-on:focus="open = true"
                x-on:keydown.escape="open = false"
                {{ $attributes->merge([
                    'class' => 'input input-xs w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden ' .
                    ($errors->has($modelid) ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : 'input-bordered')
                ]) }} />

            @if($label)
            <span>
                {{ $label }}
                @if($required)<span class="text-error">*</span>@endif
            </span>
            @endif
        </label>

        {{-- Dropdown Hasil Pencarian dengan Teleport --}}
        @if ($showdropdown)
        <template x-teleport="body">
            <ul
                x-show="open"
                wire:ignore.self {{-- Mencegah dropdown tertutup saat mengetik --}}
                x-anchor.bottom-start.offset.4="$refs.trigger"
                x-on:click.outside="open = false"
                :style="{ width: $refs.trigger.offsetWidth + 'px' }"
                class="fixed z-[9999] overflow-auto border shadow-lg rounded-box bg-base-100 max-h-60 border-base-content/10">
                {{-- Spinner Loading --}}
                <div wire:loading wire:target="{{ $modelsearch }}" class="flex flex-col items-center justify-center p-4 space-y-2 text-center">
                    <span class="loading loading-spinner loading-sm text-info"></span>
                </div>

                @if(count($options) > 0)
                @foreach ($options as $opt)
                <li
                    wire:click="{{ $clickaction }}({{ $opt->id }}, '{{ addslashes($opt->{$namedb}) }}')"
                    wire:key="opt-floating-{{ $opt->id }}"
                    x-on:click="open = false"
                    class="px-4 py-3 text-sm transition-colors border-b cursor-pointer hover:bg-base-200 active:bg-base-300 border-base-content/5 last:border-none text-base-content">
                    {{ $opt->{$namedb} }}
                </li>
                @endforeach
                @else
                {{-- Pesan jika tidak ada hasil --}}
                <div wire:loading.remove wire:target="{{ $modelsearch }}" class="px-4 py-3 text-xs italic text-gray-500">
                    {{ __('Tidak ada hasil ditemukan') }}
                </div>
                @endif
            </ul>
        </template>
        @endif
    </div>

    @if($modelid)
    <x-label-error :messages="$errors->get($modelid)" />
    @endif
</fieldset>
