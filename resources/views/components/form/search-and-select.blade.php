@props([
'label' => null,
'placeholder' => 'Cari...',
'modelsearch' => null,
'modelid' => null,
'options' => [],
'showdropdown' => false,
'required' => false,
'disabled' => false,
'columnName' => 'name',
'clickaction' => 'selectPelapor',

// Properti khusus Mode Manual
'manualMode' => false,
'manualModelName' => null,
'klikManualAction' => 'klikManualAction',
'addManualAction' => 'addManualData',
])

<fieldset class="relative fieldset md:col-span-1">
    @if($label)
    <x-form.label :label="$label" :required="$required" />
    @endif

    {{-- 1. Gunakan x-data="{ open: false }" untuk mengganti entangle yang error --}}
    <div class="relative" x-data="{ open: false }">
        <input
            x-ref="trigger"
            {{ $disabled ? 'disabled' : '' }}
            type="text"
            wire:model.live.debounce.300ms="{{ $modelsearch }}"
            placeholder="{{ __($placeholder) }}"
            {{-- Kontrol visibilitas via Alpine --}}
            x-on:focus="open = true"
            x-on:keydown.escape="open = false"
            {{ $attributes->merge([
                'class' =>
                    'input input-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs ' .
                    ($disabled ? 'bg-base-200 opacity-70 ' : '') .
                    ($errors->has($modelid) || ($manualModelName && $errors->has($manualModelName))
                        ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500'
                        : ''),
            ]) }} />

        @if(!$disabled && $showdropdown)
        <template x-teleport="body">
            <ul
                x-show="open"
                wire:ignore.self
                x-anchor.bottom-start.offset.4="$refs.trigger"
                x-on:click.outside="open = false"
                :style="{ width: $refs.trigger.offsetWidth + 'px' }"
                class="fixed z-[9999] overflow-auto border rounded-md shadow-xl bg-base-100 border-base-300 max-h-60 list-none">
                {{-- Loading State --}}
                <div wire:loading wire:target="{{ $modelsearch }}, {{ $klikManualAction }}"
                    class="flex flex-col items-center justify-center px-4 py-2 text-center">
                    <span class="loading loading-spinner loading-sm text-secondary"></span>
                </div>

                {{-- Opsi List --}}
                <div wire:loading.remove wire:target="{{ $modelsearch }}, {{ $klikManualAction }}">
                    @if(count($options) > 0)
                    @foreach($options as $opt)
                    @php
                    // Logika penggantian placeholder tetap dipertahankan
                    $action = str_replace(
                    ['VALUE_ID', 'VALUE_NAME'],
                    [$opt->id, "'" . addslashes($opt->{$columnName}) . "'"],
                    $clickaction
                    );
                    @endphp

                    <li
                        wire:click="{{ $action }}"
                        wire:key="opt-{{ $opt->id }}"
                        x-on:click="open = false"
                        class="px-3 py-1.5 text-sm cursor-pointer hover:bg-base-200 transition-colors text-base-content list-none">
                        {{ $opt->{$columnName} }}
                    </li>
                    @endforeach
                    @else
                    @if(!$manualMode)
                    <li wire:click="{{ $klikManualAction }}"
                        class="px-3 py-2 text-sm italic list-none cursor-pointer text-warning hover:bg-base-200">
                        {{ __('Tidak ditemukan, klik untuk tambah manual') }}
                    </li>
                    @endif
                    @endif
                </div>
            </ul>
        </template>
        @endif
    </div>


    <x-label-error :messages="$errors->get($modelid)" />

</fieldset>