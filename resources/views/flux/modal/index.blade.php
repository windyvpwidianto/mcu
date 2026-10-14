@props([
'dismissible' => null,
'position' => null,
'closable' => null,
'trigger' => null,
'variant' => null,
'flyout' => null,
'name' => null,
])

@php
if ($variant === 'flyout') {
    $flyout = true;
    $variant = null;
}

$closable ??= $variant === 'bare' ? false : true;
$isFlyout = (bool) ($flyout || $variant === 'flyout');

// 1. UBAH CLASS DI SINI UNTUK MENYESUAIKAN DENGAN DAISYUI
$classes = Flux::classes()
->add('modal-box relative') // daisyUI modal-box dasar + relative untuk close button
->add($isFlyout ? match($position) {
    'bottom' => 'max-w-none w-full mt-auto rounded-t-2xl rounded-b-none translate-y-0',
    'left' => 'max-w-md h-full max-h-screen mr-auto ml-0 rounded-r-2xl rounded-l-none',
    default => 'max-w-md h-full max-h-screen ml-auto mr-0 rounded-l-2xl rounded-r-none', // right
} : match ($variant) {
    'bare' => 'bg-transparent shadow-none p-0 max-w-none',
    default => 'max-w-xl max-h-[90vh] overflow-y-auto',
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

        $modalStyle = ($isFlyout ? 'height: 100vh !important;' : 'max-height: calc(100vh - 4rem) !important;') . ' overflow-y: auto !important;';
        if ($existingStyle = $styleAttributes->get('style')) {
            $modalStyle .= ' ' . $existingStyle;
        }
        $styleAttributes = $styleAttributes->merge(['style' => $modalStyle]);
        @endphp

        <ui-modal {{ $attributes }} data-flux-modal>
            @if ($trigger)
            {{ $trigger }}
            @endif

            {{-- Tambahkan class 'modal' daisyUI di tag <dialog> utama --}}
            {{-- Dan modifikasi perilaku posisi flyout via utility class daisyUI --}}
            <dialog
                wire:ignore.self
                class="modal custom-flux-modal {{ !$isFlyout ? 'modal-bottom sm:modal-middle' : '' }} {{ $isFlyout && $position === 'left' ? 'justify-start items-stretch' : '' }} {{ $isFlyout && $position !== 'left' && $position !== 'bottom' ? 'justify-end items-stretch' : '' }} {{ $isFlyout && $position === 'bottom' ? 'justify-end items-end' : '' }}"
                style="overflow-y: auto !important;"
                @if ($name) data-modal="{{ $name }}" @endif
                @if ($isFlyout) data-flux-flyout @endif
                x-data
                @if($wireModel)
                :class="{ 'modal-open': $wire.get(@js($wireModel->value())) }"
                x-effect="
                    if ($wire.get(@js($wireModel->value()))) {
                        if (!$el.open) $el.showModal();
                    } else {
                        if ($el.open) $el.close();
                    }
                "
                @close="if ($wire.get(@js($wireModel->value()))) $wire.set(@js($wireModel->value()), false)"
                @endif
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
                <div {{ $styleAttributes->class($classes) }}>
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