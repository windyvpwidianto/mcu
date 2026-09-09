@php $iconTrailing = $iconTrailing ??= $attributes->pluck('icon:trailing'); @endphp
@php $iconVariant = $iconVariant ??= $attributes->pluck('icon:variant'); @endphp

@aware([ 'variant' ])

@props([
    'iconVariant' => 'outline',
    'iconTrailing' => null,
    'badgeColor' => null,
    'variant' => null,
    'iconDot' => null,
    'accent' => true,
    'badge' => null,
    'icon' => null,
])

@php
// Button should be a square if it has no text contents...
$square ??= $slot->isEmpty();

// Size-up icons in square/icon-only buttons...
$iconClasses = Flux::classes($square ? 'size-5!' : 'size-4!');

$classes = Flux::classes()
    ->add('h-10 lg:h-8 relative flex items-center gap-3 outline-none rounded-sm')
    ->add($square ? 'px-2.5!' : '')
    ->add('py-0 text-start w-full px-3 my-px')
    ->add('text-base-content/80')
    ->add(match ($variant) {
        'outline' => match ($accent) {
            true => [
                'data-current:text-neutral-content hover:data-current:text-base-content',
                'data-current:bg-neutral data-current:border data-current:border-transparent',
                'hover:text-accent-content hover:bg-accent',
                'border border-transparent',
            ],
            false => [
                'data-current:text-neutral-content data-current:border-base-300',
                'data-current:bg-base-content/10 data-current:border data-current:border-base-content/10 data-current:shadow-sm',
                'hover:text-accent-content hover:bg-accent',
            ],
        },
        default => match ($accent) {
            true => [
                'data-current:text-neutral-content hover:data-current:text-neutral-content',
                'data-current:bg-neutral',
                'hover:text-accent-content hover:bg-accent',
            ],
            false => [
                'data-current:text-base-content',
                'data-current:bg-base-content/10',
                'hover:text-accent-content hover:bg-accent',
            ],
        },
    });
@endphp

<flux:button-or-link :attributes="$attributes->class($classes)" data-flux-navlist-item>
    @if ($icon)
        <div class="relative">
            @if (is_string($icon) && $icon !== '')
                <flux:icon :$icon :variant="$iconVariant" class="{!! $iconClasses !!}" />
            @else
                {{ $icon }}
            @endif

            @if ($iconDot)
                <div class="absolute top-[-2px] end-[-2px]">
                    {{-- Menggunakan warna brand neutral agar sinkron dengan tema --}}
                    <div class="size-[6px] rounded-sm bg-neutral-content/50"></div>
                </div>
            @endif
        </div>
    @endif

    @if ($slot->isNotEmpty())
        <div class="flex-1 text-xs font-medium leading-none whitespace-nowrap [[data-nav-footer]_&]:hidden [[data-nav-sidebar]_[data-nav-footer]_&]:block" data-content>
            {{ $slot }}
        </div>
    @endif

    @if (is_string($iconTrailing) && $iconTrailing !== '')
        <flux:icon :icon="$iconTrailing" :variant="$iconVariant" class="size-4!" />
    @elseif ($iconTrailing)
        {{ $iconTrailing }}
    @endif

    @if ($badge)
        <flux:navlist.badge :color="$badgeColor">{{ $badge }}</flux:navlist.badge>
    @endif
</flux:button-or-link>
