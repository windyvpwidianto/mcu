@php $iconTrailing = $iconTrailing ??= $attributes->pluck('icon:trailing'); @endphp
@php $iconVariant = $iconVariant ??= $attributes->pluck('icon:variant'); @endphp
@props([
    'expandable' => false,
    'expanded' => true,
    'heading' => null,
    'route' => null,
    'icon' => null,
    'iconVariant' => 'outline',
    'iconTrailing' => null,
    'iconDot' => null,
])

@if ($expandable && $heading)
    @php
        $isActive = Request::is($route);
        $square ??= $slot->isEmpty();
        $iconClasses = Flux::classes($square ? 'size-5!' : 'size-4!');
    @endphp
    <ui-disclosure {{ $attributes->class('group/disclosure') }} {{ $isActive ? 'open' : '' }} data-flux-navlist-group>
        <button type="button"
            class="w-full h-10 lg:h-8 flex items-center group/disclosure-button mb-[2px] rounded-sm transition-colors relative
            {{ $isActive
                ? 'bg-neutral text-neutral-content font-semibold'
                : 'text-base-content/80 hover:bg-accent hover:text-accent-content' }}">

            @if ($icon)
                <div class="relative ps-3 pe-4">
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
            <span class=" text-xs font-medium leading-none">{{ $heading }}</span>
            <div class="absolute inset-y-0 right-0 flex items-center pe-3">
                {{-- Toggle icon berdasarkan state open/closed --}}
                <flux:icon.chevron-down class="size-3! hidden group-data-open/disclosure:block" />
                <flux:icon.chevron-right class="size-3! block group-data-open/disclosure:hidden" />
            </div>
        </button>

        <div class="relative hidden data-open:block space-y-[2px] ps-7"
            @if ($expanded === true) data-open @endif>
            {{-- Garis indikator vertikal (Sub-menu line) --}}
            <div class="absolute inset-y-[3px] w-px bg-base-content/10 start-0 ms-4"></div>

            {{ $slot }}
        </div>
    </ui-disclosure>
@elseif ($heading)
    <div {{ $attributes->class('block space-y-[2px]') }}>
        <div class="px-3 py-2">
            {{-- Header statis jika tidak expandable --}}
            <div class="text-xs font-bold leading-none tracking-wider uppercase text-base-content/50">
                {{ $heading }}
            </div>
        </div>

        <div>
            {{ $slot }}
        </div>
    </div>
@else
    <div {{ $attributes->class('block space-y-[2px]') }}>
        {{ $slot }}
    </div>
@endif
