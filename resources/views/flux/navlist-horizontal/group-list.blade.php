@props([
'expandable' => false,
'expanded' => true,
'heading' => null,
'route'=>null,
])

<?php if ($expandable && $heading): ?>
<ui-disclosure {{ $attributes->class('group/disclosure') }} {{ Request::is($route) ? 'open' : '' }} data-flux-navlist-group>
    <button type="button" class="w-full {{ Request::is($route) ? 'bg-neutral text-(--color-neutral-content)' : '' }}  h-10 lg:h-8 flex items-center group/disclosure-button mb-[2px] rounded-sm hover:bg-zinc-800/5 dark:hover:bg-accent  hover:text-(--color-neutral-content)">
        <div class="ps-3 pe-4">
            <flux:icon.chevron-down class="size-3!  hidden group-data-open/disclosure-button:block" />
            <flux:icon.chevron-right class="size-3! block group-data-open/disclosure-button:hidden" />
        </div>

        <span class="text-xs font-medium leading-none {{ Request::is($route) ? 'text-(--color-neutral-content)  font-semibold' : '' }}">{{ $heading }}</span>
    </button>

    <div class="relative hidden data-open:block space-y-[2px] ps-7" @if ($expanded===true) data-open @endif>
        <div class="absolute inset-y-[3px] w-px bg-zinc-200 dark:bg-(--color-neutral-content)/30 start-0 ms-4"></div>

        {{ $slot }}
    </div>
</ui-disclosure>
<?php elseif ($heading): ?>
<div {{ $attributes->class('block space-y-[2px]') }}>
    <div class="px-3 py-2">
        <div class="text-sm text-zinc-400 font-medium leading-none">{{ $heading }}</div>
    </div>

    <div>
        {{ $slot }}
    </div>
</div>
<?php else: ?>
<div {{ $attributes->class('block space-y-[2px]') }}>
    {{ $slot }}
</div>
<?php endif; ?>
