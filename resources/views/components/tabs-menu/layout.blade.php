<div class="flex flex-col ">
     <div  class=" hidden md:block w-60">
        <flux:navlist-horizontal>
            <flux:navlist-horizontal.item :href="route('administration-menu')"  :current="request()->routeIs(  'administration-menu' )"  wire:navigate>{{ __('Menu') }}</flux:navlist-horizontal.item>
            <flux:navlist-horizontal.item :href="route('administration-menu-submenu')" :current="request()->routeIs(  'administration-menu-submenu' )"  wire:navigate>{{ __('Sub Menu') }}</flux:navlist-horizontal.item>
            <flux:navlist-horizontal.item :href="route('administration-menu-extrasubmenu')" :current="request()->routeIs(  'administration-menu-extrasubmenu' )"  wire:navigate>{{ __('Extra Sub menu') }}</flux:navlist-horizontal.item>
        </flux:navlist-horizontal>
    </div>
   <div   class=" md:hidden w-full">
        <flux:navlist>
            <flux:navlist-horizontal.item :href="route('administration-menu')"  :current="request()->routeIs(  'administration-menu' )"  wire:navigate>{{ __('Menu') }}</flux:navlist-horizontal.item>
            <flux:navlist-horizontal.item :href="route('administration-menu-submenu')" :current="request()->routeIs(  'administration-menu-submenu' )"  wire:navigate>{{ __('Sub Menu') }}</flux:navlist-horizontal.item>
            <flux:navlist-horizontal.item :href="route('administration-menu-extrasubmenu')" :current="request()->routeIs(  'administration-menu-extrasubmenu' )"  wire:navigate>{{ __('Extra Sub menu') }}</flux:navlist-horizontal.item>
        </flux:navlist>
    </div>
    {{-- <flux:separator class="md:hidden" /> --}}
    <div class=" p-2 ">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading size='xs'>{{ $subheading ?? '' }}</flux:subheading>
        <div class="mt-5  w-full ">
            {{ $slot }}
        </div>
    </div>
</div>
