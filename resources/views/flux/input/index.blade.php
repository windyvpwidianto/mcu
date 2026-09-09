@php $iconTrailing = $iconTrailing ??= $attributes->pluck('icon:trailing'); @endphp
@php $iconLeading = $iconLeading ??= $attributes->pluck('icon:leading'); @endphp
@php $iconVariant = $iconVariant ??= $attributes->pluck('icon:variant'); @endphp

@props([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'iconVariant' => 'mini',
    'variant' => 'outline',
    'iconTrailing' => null,
    'iconLeading' => null,
    'expandable' => null,
    'clearable' => null,
    'copyable' => null,
    'viewable' => null,
    'invalid' => null,
    'loading' => null,
    'type' => 'text',
    'mask' => null,
    'size' => null,
    'icon' => null,
    'kbd' => null,
    'as' => null,
])

@php
    $wireModel = $attributes->wire('model');
    $wireTarget = null;

    if ($loading !== false) {
        if ($loading === true) {
            $loading = true;
        } elseif ($wireModel?->directive) {
            $loading = $wireModel->hasModifier('live');
            $wireTarget = $loading ? $wireModel->value() : null;
        } else {
            $wireTarget = $loading;
            $loading = (bool) $loading;
        }
    }

    $invalid ??= $name && $errors->has($name);
    $iconLeading ??= $icon;
    $hasLeadingIcon = (bool) $iconLeading;

    $countOfTrailingIcons = collect([
        (bool) $iconTrailing,
        (bool) $kbd,
        (bool) $clearable,
        (bool) $copyable,
        (bool) $viewable,
        (bool) $expandable,
    ])
        ->filter()
        ->count();

    // Icon Classes: Mengikuti warna konten dasar, berubah menjadi emas (primary) saat focus
    $iconClasses = Flux::classes('text-[var(--color-base-content)]/50 group-focus-within/input:text-[var(--color-primary)] transition-colors duration-200')
        ->add($iconVariant === 'outline' ? 'size-5' : '');

    $inputLoadingClasses = Flux::classes()->add(
        match ($countOfTrailingIcons) {
            0 => 'pe-10', 1 => 'pe-16', 2 => 'pe-23', 3 => 'pe-30', 4 => 'pe-37', 5 => 'pe-44', 6 => 'pe-51',
        },
    );

    $classes = Flux::classes()
        // Integrasi Tema DaisyUI
        ->add('input input-bordered w-full max-w-sm block disabled:shadow-none dark:shadow-none transition-all duration-200')
        // Focus State: Golden Glow Effect
        ->add('focus:outline-none focus:border-[var(--color-primary)] focus:ring-[3px] focus:ring-[var(--color-primary)]/20 focus-within:border-[var(--color-primary)] focus-within:ring-[3px] focus-within:ring-[var(--color-primary)]/20')
        ->add('appearance-none')
        ->add(
            match ($size) {
                'xs' => 'input-xs text-xs py-1 h-6 leading-tight',
                'sm' => 'input-sm text-sm py-1.5 h-8',
                default => 'text-[var(--color-base-content)] sm:text-sm py-2 h-10 leading-[1.375rem]',
            },
        )
        ->add($hasLeadingIcon ? 'ps-10' : 'ps-3')
        ->add(
            match ($countOfTrailingIcons) {
                0 => 'pe-3', 1 => 'pe-10', 2 => 'pe-16', 3 => 'pe-23', 4 => 'pe-30', 5 => 'pe-37', 6 => 'pe-44',
            },
        )
        ->add(
            match ($variant) {
                'outline' => 'bg-[var(--color-base-100)] dark:bg-transparent dark:disabled:bg-[var(--color-base-content)]/5',
                'filled' => 'bg-[var(--color-base-200)] border-transparent hover:bg-[var(--color-base-300)]',
            },
        )
        ->add(
            match ($variant) {
                'outline' => 'text-[var(--color-base-content)] disabled:text-[var(--color-base-content)]/40 placeholder-[var(--color-base-content)]/30',
                'filled' => 'text-[var(--color-base-content)] placeholder-[var(--color-base-content)]/40',
            },
        )
        ->add(
            match ($variant) {
                'outline' => $invalid ? 'border-[var(--color-error)]' : 'border-[var(--color-base-300)] shadow-xs',
                'filled' => $invalid ? 'border-[var(--color-error)]' : 'border-0',
            },
        )
        ->add($attributes->pluck('class:input'));
@endphp

<?php if ($type === 'file'): ?>
<flux:with-field :$attributes :$name>
    <flux:input.file :$attributes :$name :$size />
</flux:with-field>
<?php elseif ($as !== 'button'): ?>
<flux:with-field :$attributes :$name>
    <div {{ $attributes->only('class')->class('w-full relative block group/input') }} data-flux-input>
        @if ($iconLeading)
        <div class="absolute top-0 bottom-0 flex items-center justify-center pointer-events-none ps-3 start-0">
             @if (is_string($iconLeading))
                <flux:icon :icon="$iconLeading" :variant="$iconVariant" :class="$iconClasses" />
            @else
                <div class="{{ $iconClasses }}"> {{ $iconLeading }} </div>
            @endif
        </div>
        @endif

        <input type="{{ $type }}" {{ $attributes->except('class')->class($type === 'file' ? '' : $classes) }}
            @isset($name) name="{{ $name }}" @endisset
            @if ($mask) x-mask="{{ $mask }}" @endif
            @if ($invalid) aria-invalid="true" data-invalid @endif
            @if (is_numeric($size)) size="{{ $size }}" @endif data-flux-control data-flux-group-target
            @if ($loading) wire:loading.class="{{ $inputLoadingClasses }}" @endif
            @if ($loading && $wireTarget) wire:target="{{ $wireTarget }}" @endif>

        <div class="absolute top-0 bottom-0 flex items-center gap-x-1.5 pe-3 end-0">
            @if ($loading)
                <flux:icon name="loading" :variant="$iconVariant" :class="$iconClasses" wire:loading :wire:target="$wireTarget" />
            @endif

            @if ($clearable)
                <flux:input.clearable inset="left right" :$size />
            @endif

            @if ($kbd)
                <kbd class="kbd kbd-sm bg-[var(--color-base-300)] border-[var(--color-base-content)]/10 text-[var(--color-base-content)]/60 text-[10px]">{{ $kbd }}</kbd>
            @endif

            @if ($expandable) <flux:input.expandable inset="left right" :$size /> @endif
            @if ($copyable) <flux:input.copyable inset="left right" :$size /> @endif
            @if ($viewable) <flux:input.viewable inset="left right" :$size /> @endif

            @if (is_string($iconTrailing))
                <flux:icon :icon="$iconTrailing" :variant="$iconVariant" :class="$iconClasses" />
            @elseif ($iconTrailing)
                <div class="{{ $iconClasses }}"> {{ $iconTrailing }} </div>
            @endif
        </div>
    </div>
</flux:with-field>
<?php else: ?>
<button {{ $attributes->merge(['type' => 'button'])->class([$classes, 'w-full relative flex items-center']) }}>
    @if ($iconLeading)
        <div class="absolute top-0 bottom-0 flex items-center justify-center ps-3 start-0">
            <flux:icon :icon="is_string($iconLeading) ? $iconLeading : ''" :variant="$iconVariant" :class="$iconClasses" />
            @if (!is_string($iconLeading)) {{ $iconLeading }} @endif
        </div>
    @endif

    <div class="self-center flex-1 font-medium text-start truncate {{ $attributes->has('placeholder') ? 'text-[var(--color-base-content)]/40' : 'text-[var(--color-base-content)]' }}">
        {{ $attributes->get('placeholder') ?? $slot }}
    </div>

    @if ($kbd)
        <kbd class="kbd kbd-xs bg-[var(--color-base-200)] mx-2">{{ $kbd }}</kbd>
    @endif

    @if ($iconTrailing)
        <div class="ms-2">
            <flux:icon :icon="is_string($iconTrailing) ? $iconTrailing : ''" :variant="$iconVariant" :class="$iconClasses" />
            @if (!is_string($iconTrailing)) {{ $iconTrailing }} @endif
        </div>
    @endif
</button>
<?php endif; ?>
