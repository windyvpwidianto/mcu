@props([
'dismissible' => null,
'position' => null,
'closable' => null,
'trigger' => null,
'variant' => null,
'name' => null,
])

@php
$closable ??= $variant === 'bare' ? false : true;

// 1. UBAH CLASS DI SINI UNTUK MENYESUAIKAN DENGAN DAISYUI
$classes = Flux::classes()
->add('modal-box relative') // daisyUI modal-box dasar + relative untuk close button
->add(match ($variant) {
// Default Modal daisyUI (Tengah)
default => 'max-w-xl',

// Flyout / Drawer style (daisyUI biasanya pakai komponen drawer,
// tapi kita bisa manipulasi modal-box agar mepet ke samping)
'flyout' => match($position) {
'bottom' => 'max-w-none w-full mt-auto rounded-t-2xl rounded-b-none translate-y-0',
'left' => 'max-w-md h-full max-h-screen mr-auto ml-0 rounded-r-2xl rounded-l-none',
default => 'max-w-md h-full max-h-screen ml-auto mr-0 rounded-l-2xl rounded-r-none', // right
},
'bare' => 'bg-transparent shadow-none p-0 max-w-none',
})
// Menggunakan sistem pewarnaan / tema dari daisyUI (misal: bg-base-100)
->add(match ($variant) {
default => 'bg-base-100 text-base-content border border-base-200',
'flyout' => 'bg-base-100 text-base-content border-base-200',
'bare' => 'bg-transparent',
});

// Support adding the .self modifier to the wire:model directive...
if (($wireModel = $attributes->wire('model')) && $wireModel->directive && ! $wireModel->hasModifier('self')) {
unset($attributes[$wireModel->directive]);

$wireModel->directive .= '.self';

$attributes = $attributes->merge([$wireModel->directive => $wireModel->value]);
}

// Support <flux:modal ... @close="?"> syntax...
    if ($attributes['@close'] ?? null) {
    $attributes['wire:close'] = $attributes['@close'];
    unset($attributes['@close']);
    }

    // Support <flux:modal ... @cancel="?"> syntax...
        if ($attributes['@cancel'] ?? null) {
        $attributes['wire:cancel'] = $attributes['@cancel'];
        unset($attributes['@cancel']);
        }

        if ($dismissible === false) {
        $attributes = $attributes->merge(['disable-click-outside' => '']);
        }

        [ $styleAttributes, $attributes ] = Flux::splitAttributes($attributes, ['autofocus', 'class', 'style', 'wire:close', 'x-on:close', 'wire:cancel', 'x-on:cancel']);
        @endphp

        <ui-modal {{ $attributes }} data-flux-modal>
            @if ($trigger)
            {{ $trigger }}
            @endif

            {{-- Tambahkan class 'modal' daisyUI di tag <dialog> utama --}}
            {{-- Dan modifikasi perilaku posisi flyout via utility class daisyUI --}}
            <dialog
                wire:ignore.self
                {{ $styleAttributes->class([
            'modal custom-flux-modal',
            'modal-bottom sm:modal-middle' => $variant !== 'flyout', // perilaku responsive daisyUI asli
            'justify-start items-stretch' => $variant === 'flyout' && $position === 'left',
            'justify-end items-stretch' => $variant === 'flyout' && $position !== 'left' && $position !== 'bottom',
            'justify-end items-end' => $variant === 'flyout' && $position === 'bottom',
        ]) }}
                @if ($name) data-modal="{{ $name }}" @endif
                @if ($variant==='flyout' ) data-flux-flyout @endif
                x-data
                @isset($__livewire)
                x-on:modal-show.document="
                if ($event.detail.name === @js($name) && ($event.detail.scope === @js($__livewire->getId()))) $el.showModal();
                if ($event.detail.name === @js($name) && (! $event.detail.scope)) $el.showModal();
            "
                x-on:modal-close.document="
                if ($event.detail.name === @js($name) && ($event.detail.scope === @js($__livewire->getId()))) $el.close();
                if (! $event.detail.name || ($event.detail.name === @js($name) && (! $event.detail.scope))) $el.close();
            "
                @else
                x-on:modal-show.document="if ($event.detail.name === @js($name) && (! $event.detail.scope)) $el.showModal()"
                x-on:modal-close.document="if (! $event.detail.name || ($event.detail.name === @js($name) && (! $event.detail.scope))) $el.close()"
                @endif>
                {{-- Wrapper konten dengan class modal-box daisyUI yang sudah kita racik di atas --}}
                <div class="{{ $classes }}">
                    {{ $slot }}

                    @if ($closable)
                    <div class="absolute m-2 top-2 right-2">
                        <flux:modal.close>
                            {{-- Menggunakan class btn-sm btn-circle btn-ghost dari daisyUI --}}
                            <button type="button" class="btn btn-sm btn-circle btn-ghost text-base-content/70 hover:text-base-content">✕</button>
                        </flux:modal.close>
                    </div>
                    @endif
                </div>

                {{-- Backdrop daisyUI (Klik di luar untuk menutup otomatis didukung jika dismissible) --}}
                @if ($dismissible !== false)
                <form method="dialog" class="modal-backdrop bg-neutral/40 backdrop-blur-xs">
                    <flux:modal.close>
                        <button>close</button>
                    </flux:modal.close>
                </form>
                @else
                <div class="pointer-events-none modal-backdrop bg-neutral/40 backdrop-blur-xs"></div>
                @endif
            </dialog>
        </ui-modal>