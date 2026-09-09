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
'clickaction' => null,

// Properti khusus Mode Manual
'manualMode' => false,
'manualModelName' => null,
'enableManualAction' => null,
'addManualAction' => null,
])

<fieldset class="relative fieldset md:col-span-1">
    @if($label)
    <x-form.label :label="$label" :required="$required" />
    @endif

    <div x-data="{
        open: false,
        triggerWidth: '100%',
        init() {
            this.triggerWidth = this.$refs.trigger.offsetWidth + 'px';
            new ResizeObserver(() => {
                if (this.$refs.trigger) {
                    this.triggerWidth = this.$refs.trigger.offsetWidth + 'px';
                }
            }).observe(this.$refs.trigger);
        },
        focusManualInput() {
            this.$nextTick(() => {
                if (this.$refs.manualInput) {
                    this.$refs.manualInput.focus();
                }
            });
        }
    }" class="relative">

        {{-- Input Utama (Trigger) --}}
        <input
            x-ref="trigger"
            {{ $disabled ? 'disabled' : '' }}
            type="text"
            wire:model.live.debounce.300ms="{{ $modelsearch }}"
            placeholder="{{ __($placeholder) }}"
            x-on:focus="open = true"
            x-on:keydown.escape.window="open = false"
            {{ $attributes->merge([
                'class' => 'input input-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs ' .
                    ($disabled ? 'bg-base-200 opacity-70 ' : '') .
                    ($errors->has($modelid) || ($manualModelName && $errors->has($manualModelName))
                        ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500'
                        : ''),
            ]) }} />

        {{-- Dropdown Teleport --}}
        @if(!$disabled && $showdropdown)
        <template x-teleport="body">
            <ul
                x-show="open"
                x-cloak
                wire:ignore.self
                x-anchor.bottom-start.offset.4="$refs.trigger"
                x-on:click.outside="open = false"
                :style="{ width: triggerWidth }"
                class="fixed z-[9999] overflow-auto border rounded-md shadow-xl bg-base-100 border-base-300 max-h-60 list-none">

                {{-- Loading State --}}
                <li wire:loading wire:target="{{ $modelsearch }}, {{ $enableManualAction }}"
                    class="flex flex-col items-center justify-center px-4 py-2 text-center list-none">
                    <span class="loading loading-spinner loading-sm text-secondary"></span>
                </li>

                {{-- Opsi List --}}
                <div wire:loading.remove wire:target="{{ $modelsearch }}, {{ $enableManualAction }}">
                    @if(count($options) > 0)
                    @foreach($options as $opt)
                    @php
                    $action = str_replace(
                    ['VALUE_ID', 'VALUE_NAME'],
                    [$opt->id, "'" . addslashes($opt->{$columnName}) . "'"],
                    $clickaction
                    );
                    @endphp

                    {{-- Kunci Solusi: Gabungkan wire:click dan x-on:click langsung --}}
                    <li
                        wire:key="opt-{{ $opt->id }}"
                        wire:click="{{ $action }}"
                        x-on:click="open = false"
                        class="px-3 py-1.5 text-sm cursor-pointer hover:bg-base-200 transition-colors text-base-content list-none">
                        {{ $opt->{$columnName} }}
                    </li>
                    @endforeach
                    @else
                    @if(!$manualMode)
                    <li wire:click="{{ $enableManualAction }}"
                        x-on:click="focusManualInput()"
                        class="px-3 py-2 text-sm italic list-none cursor-pointer text-warning hover:bg-base-200">
                        {{ __('Tidak ditemukan, klik untuk tambah manual') }}
                    </li>
                    @endif
                    @endif

                    {{-- Input Manual Field --}}
                    @if($manualMode)
                    <li class="p-2 list-none border-t border-base-300 bg-base-200/50" x-on:click.stop>
                        <div class="flex items-center gap-1">
                            <input
                                x-ref="manualInput"
                                type="text"
                                wire:model.live="{{ $manualModelName }}"
                                placeholder="{{ __('Nama manual...') }}"
                                wire:keydown.enter.prevent="{{ $addManualAction }}"
                                x-on:keydown.enter="open = false"
                                class="w-full input input-bordered input-xs focus:ring-1 focus:ring-info bg-base-100" />

                            <button
                                type="button"
                                wire:click="{{ $addManualAction }}"
                                x-on:click="open = false"
                                class="btn btn-primary btn-xs">
                                {{ __('Tambah') }}
                            </button>
                        </div>
                    </li>
                    @endif
                </div>
            </ul>
        </template>
        @endif
    </div>

    @if($manualMode && $manualModelName)
    <x-label-error :messages="$errors->get($manualModelName)" />
    @else
    <x-label-error :messages="$errors->get($modelid)" />
    @endif
</fieldset>