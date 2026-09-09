@php $iconTrailing = $iconTrailing ??= $attributes->pluck('icon:trailing'); @endphp
@php $iconLeading = $iconLeading ??= $attributes->pluck('icon:leading'); @endphp
@php $iconVariant = $iconVariant ??= $attributes->pluck('icon:variant'); @endphp

@props([
'iconTrailing' => null,
'variant' => 'outline',
'iconVariant' => null,
'iconLeading' => null,
'type' => 'button',
'loading' => null,
'size' => 'base',
'square' => null,
'inset' => null,
'icon' => null,
'kbd' => null,
])

@php
$iconLeading = $icon ??= $iconLeading;
$square ??= $slot->isEmpty();

$iconVariant ??= ($size === 'xs')
? ($square ? 'micro' : 'micro')
: ($square ? 'mini' : 'micro');

$iconClasses = Flux::classes()
->add($iconVariant === 'outline' ? ($square && $size !== 'xs' ? 'size-5' : 'size-4') : '');

$isTypeSubmitAndNotDisabledOnRender = $type === 'submit' && ! $attributes->has('disabled');
$isJsMethod = str_starts_with($attributes->whereStartsWith('wire:click')->first() ?? '', '$js.');
$loading ??= $loading ?? ($isTypeSubmitAndNotDisabledOnRender || $attributes->whereStartsWith('wire:click')->isNotEmpty() && ! $isJsMethod);

if ($loading && $type !== 'submit' && ! $isJsMethod) {
$attributes = $attributes->merge(['wire:loading.attr' => 'data-flux-loading']);
if (! $attributes->has('wire:target') && $target = $attributes->whereStartsWith('wire:click')->first()) {
$attributes = $attributes->merge(['wire:target' => $target], escape: false);
}
}

$classes = Flux::classes()
/* --- CORE DAISYUI CLASS --- */
->add('btn relative inline-flex items-center justify-center gap-2 whitespace-nowrap transition-all duration-200 active:scale-[0.95]')
->add('disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none overflow-hidden')

/* --- SIZING --- */
->add(match ($size) {
'xs' => 'btn-xs',
'sm' => 'btn-sm',
'base' => '',
'lg' => 'btn-lg',
'xl' => 'btn-xl px-8 h-16 text-lg',
default => '',
})
->add($square ? 'btn-square' : '')

/* --- THEME DESIGN SYSTEM (DEPTH, NOISE, RADIUS) --- */
->add('rounded-[var(--radius-field)] border-[length:var(--border)]')
// Efek Depth (Elevasi dinamis)
->add('shadow-[0_calc(var(--depth)*1px)_calc(var(--depth)*2px)_rgba(0,0,0,0.25)]')
// Efek Noise (Tekstur Material) menggunakan pseudo-element
->add('before:content-[""] before:absolute before:inset-0 before:pointer-events-none before:opacity-[calc(var(--noise)*0.05)] before:bg-[url("https://grainy-gradients.vercel.app/noise.svg")] before:mix-blend-overlay')

/* --- VARIANTS & COLORS --- */
->add(match ($variant) {
'primary' => 'bg-[var(--color-primary)] hover:bg-[color-mix(in_oklab,var(--color-primary),black_10%)] text-[var(--color-primary-content)] border-black/10',
'accent' => 'bg-[var(--color-accent)] hover:bg-[color-mix(in_oklab,var(--color-accent),black_10%)] text-[var(--color-accent-content)] border-black/10',
'secondary' => 'bg-[var(--color-secondary)] hover:bg-[color-mix(in_oklab,var(--color-secondary),black_10%)] text-[var(--color-secondary-content)] border-black/10',
'danger' => 'bg-[var(--color-error)] hover:bg-[color-mix(in_oklab,var(--color-error),black_10%)] text-[var(--color-error-content)] border-black/10',
'success' => 'bg-[var(--color-success)] hover:bg-[color-mix(in_oklab,var(--color-success),black_10%)] text-[var(--color-success-content)] border-black/10',
'warning' => 'bg-[var(--color-warning)] hover:bg-[color-mix(in_oklab,var(--color-warning),black_10%)] text-[var(--color-warning-content)] border-black/10',
'info' => 'bg-[var(--color-info)] hover:bg-[color-mix(in_oklab,var(--color-info),black_10%)] text-[var(--color-info-content)] border-black/10',
'filled' => 'bg-[var(--color-base-300)] hover:bg-[var(--color-base-content)]/10 text-[var(--color-base-content)] border-transparent',
'outline' => 'bg-transparent border-[var(--color-base-300)] hover:bg-[var(--color-base-200)] text-[var(--color-base-content)]',
'ghost' => 'bg-transparent border-transparent hover:bg-[var(--color-base-content)]/10 text-[var(--color-base-content)] shadow-none',
'subtle' => 'bg-[var(--color-primary)]/10 hover:bg-[var(--color-primary)]/20 text-[var(--color-primary)] border-transparent shadow-none',
})

/* --- INSET LOGIC --- */
->add($inset ? match ($size) {
'base' => $square
? Flux::applyInset($inset, top: '-mt-2.5', right: '-me-2.5', bottom: '-mb-2.5', left: '-ms-2.5')
: Flux::applyInset($inset, top: '-mt-2.5', right: '-me-4', bottom: '-mb-3', left: '-ms-4'),
'sm' => $square
? Flux::applyInset($inset, top: '-mt-1.5', right: '-me-1.5', bottom: '-mb-1.5', left: '-ms-1.5')
: Flux::applyInset($inset, top: '-mt-1.5', right: '-me-3', bottom: '-mb-1.5', left: '-ms-3'),
'xs' => $square
? Flux::applyInset($inset, top: '-mt-1', right: '-me-1', bottom: '-mb-1', left: '-ms-1')
: Flux::applyInset($inset, top: '-mt-1', right: '-me-2', bottom: '-mb-1', left: '-ms-2'),
} : '')

->add($loading ? [
'*:transition-opacity',
$type === 'submit' ? '[&[disabled]>:not([data-flux-loading-indicator])]:opacity-0' : '[&[data-flux-loading]>:not([data-flux-loading-indicator])]:opacity-0',
$type === 'submit' ? '[&[disabled]>[data-flux-loading-indicator]]:opacity-100' : '[&[data-flux-loading]>[data-flux-loading-indicator]]:opacity-100',
$type === 'submit' ? '[&[disabled]]:pointer-events-none' : 'data-flux-loading:pointer-events-none',
] : []);

$attributes = $attributes->merge([
'data-flux-group-target' => ! in_array($variant, ['subtle', 'ghost']),
]);
@endphp

<flux:with-tooltip :$attributes>
    <flux:button-or-link :$type :attributes="$attributes->class($classes)" data-flux-button>
        @if ($loading)
        <div class="absolute inset-0 z-10 flex items-center justify-center opacity-0" data-flux-loading-indicator>
            <flux:icon icon="loading" :variant="$iconVariant" :class="$iconClasses" />
        </div>
        @endif

        @if (is_string($iconLeading) && $iconLeading !== '')
        <flux:icon :icon="$iconLeading" :variant="$iconVariant" :class="$iconClasses" />
        @elseif ($iconLeading)
        {{ $iconLeading }}
        @endif

        @if ($loading && ! $slot->isEmpty())
        <span class="relative z-0">{{ $slot }}</span>
        @else
        {{ $slot }}
        @endif

        @if ($kbd)
        <div class="text-[10px] opacity-60 border border-current/20 px-1 rounded-sm bg-[var(--color-base-200)]">{{ $kbd }}</div>
        @endif

        @if (is_string($iconTrailing) && $iconTrailing !== '')
        @php $iconClasses->add($square ? '' : '-ms-1'); @endphp
        <flux:icon :icon="$iconTrailing" :variant="$iconVariant" :class="$iconClasses" />
        @elseif ($iconTrailing)
        {{ $iconTrailing }}
        @endif
    </flux:button-or-link>
</flux:with-tooltip>