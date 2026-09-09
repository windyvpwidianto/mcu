@php $iconTrailing = $iconTrailing ??= $attributes->pluck('icon:trailing'); @endphp
@php $iconVariant = $iconVariant ??= $attributes->pluck('icon:variant'); @endphp

@props([
    'iconTrailing' => null,
    'iconVariant' => 'mini',
    'variant' => 'default',
    'suffix' => null,
    'accent' => true,
    'value' => null,
    'icon' => null,
    'kbd' => null,
])

@php
if ($kbd) $suffix = $kbd;

$iconClasses = Flux::classes()
    ->add('me-2')
    ->add($iconVariant === 'outline' ? 'size-5' : null);

$trailingIconClasses = Flux::classes()
    ->add('ms-auto text-base-content/50 [[data-flux-menu-item-icon]:hover_&]:text-current')
    ->add($iconVariant === 'outline' ? 'size-5' : null);

$classes = Flux::classes()
    ->add('flex items-center px-2 py-1.5 gap-4 w-full focus:outline-hidden capitalize')
    ->add('rounded-sm')
    ->add('text-start text-xs font-medium')
    ->add('[&[disabled]]:opacity-50')
    ->add(match ($variant) {
        'outline' => match ($accent) {
            true => [
                'data-current:text-neutral-content hover:data-current:text-base-content',
                'data-current:bg-neutral/30 data-current:border-neutral data-current:border-b-4 data-current:border-transparent',
                'hover:text-base-content hover:bg-base-content/5',
                'border border-transparent',
            ],
            false => [
                'data-current:text-base-content data-current:border-base-300',
                'data-current:bg-base-content/10 data-current:border data-current:border-base-content/10 data-current:shadow-sm',
                'hover:text-base-content',
            ],
        },
        default => match ($accent) {
            true => [
                'data-current:text-base-content hover:data-current:text-base-content',
                'data-current:border-neutral data-current:border-b-4',
                'hover:text-accent-content hover:bg-accent text-neutral',
            ],
            false => [
                'data-current:text-base-content',
                'data-current:bg-base-content/10',
                'hover:text-accent-content hover:bg-accent',
            ],
        },
    });

$suffixClasses = Flux::classes()
    ->add('ms-auto text-xs text-base-content/50');
@endphp

<flux:button-or-link :attributes="$attributes->class($classes)" data-flux-menu-item :data-flux-menu-item-has-icon="!! $icon">
    @if (is_string($icon) && $icon !== '')
        <flux:icon :$icon :variant="$iconVariant" :class="$iconClasses" data-flux-menu-item-icon />
    @elseif ($icon)
        {{ $icon }}
    @else
        <div class="w-7 hidden [[data-flux-menu]:has(>[data-flux-menu-item-has-icon])_&]:block"></div>
    @endif

    {{ $slot }}

    @if ($suffix)
        @if (is_string($suffix))
            <div class="{{ $suffixClasses }}">
                {{ $suffix }}
            </div>
        @else
            {{ $suffix }}
        @endif
    @endif

    @if (is_string($iconTrailing) && $iconTrailing !== '')
        <flux:icon :icon="$iconTrailing" :variant="$iconVariant" :class="$trailingIconClasses" data-flux-menu-item-icon />
    @elseif ($iconTrailing)
        {{ $iconTrailing }}
    @endif

    {{ $submenu ?? '' }}
</flux:button-or-link>
