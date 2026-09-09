@props([
'label' => null,
'placeholder' => 'Cari...',
'modelsearch' => null,
'modelid' => null,
'options' => [],
'showdropdown' => false,
'required' => false,
'disabled' => false,
'clickaction' => 'selectLocation',
'namedb' => 'name',
])

<fieldset class="relative fieldset md:col-span-1">
    @if ($label)
    <x-form.label :label="$label" :required="$required" />
    @endif

    {{-- Integrasi Alpine.js dengan ResizeObserver untuk sinkronisasi lebar dropdown --}}
    <div class="relative mb-0.5" x-data="{
        open: false,
        triggerWidth: '100%',
        init() {
            this.triggerWidth = this.$refs.trigger.offsetWidth + 'px';
            new ResizeObserver(() => {
                if (this.$refs.trigger) {
                    this.triggerWidth = this.$refs.trigger.offsetWidth + 'px';
                }
            }).observe(this.$refs.trigger);
        }
    }">

        {{-- Input Search --}}
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
                    ($errors->has($modelid)
                        ? 'border-rose-500 ring-1 ring-rose-500 focus:border-rose-500 focus:ring-rose-500'
                        : 'border-gray-300'),
            ]) }} />

        {{-- Dropdown Teleport --}}
        @if (!$disabled && $showdropdown)
        <template x-teleport="body">
            <ul
                x-show="open"
                x-cloak
                wire:ignore.self
                x-anchor.bottom-start.offset.4="$refs.trigger"
                x-on:click.outside="open = false"
                :style="{ width: triggerWidth }"
                class="fixed z-[99999] overflow-auto border rounded-md shadow-2xl bg-base-100 border-base-300 max-h-60 list-none">

                {{-- Spinner Loading (Aktif saat mengetik ATAU saat memilih opsi) --}}
                <div class="flex flex-col items-center justify-center px-4 py-2 text-center">
                    <span wire:loading.remove.class="hidden" wire:target="{{ $modelsearch }}, {{ $clickaction }}" class="loading loading-spinner loading-sm text-secondary hidden"></span>
                </div>

                {{-- List Hasil Pencarian (Disembunyikan sementara saat proses klik berlangsung) --}}
                <div wire:loading.remove wire:target="{{ $clickaction }}">
                    @if (count($options) > 0)
                    @foreach ($options as $opt)
                    <li wire:click="{{ $clickaction }}({{ $opt->id }}, '{{ addslashes($opt->{$namedb}) }}')"
                        wire:key="opt-{{ $modelid }}-{{ $opt->id }}"
                        x-on:click="open = false"
                        class="px-3 py-2 text-sm transition-colors border-b cursor-pointer hover:bg-base-200 text-base-content border-base-200 last:border-0 list-none">
                        {{ $opt->{$namedb} }}
                    </li>
                    @endforeach
                    @else
                    <li wire:loading.remove wire:target="{{ $modelsearch }}"
                        class="px-3 py-2 text-sm italic text-warning bg-base-100 list-none">
                        {{ __('Data tidak ditemukan') }}
                    </li>
                    @endif
                </div>
            </ul>
        </template>
        @endif
    </div>

    @if ($modelid)
    <x-label-error :messages="$errors->get($modelid)" />
    @endif
</fieldset>