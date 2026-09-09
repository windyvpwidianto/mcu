@props(['activeTab', 'heading', 'subheading'])
<div class="flex flex-col ">
    <div class="w-full md:w-60">
        <div class="hidden md:block w-60">
            <flux:navlist-horizontal>
                <flux:navlist-horizontal.item :href="route('hazard.workflows')" wire:navigate>{{ __('Hazard Workflow Administration') }}</flux:navlist-horizontal.item>
                <flux:navlist-horizontal.item :href="route('wpi.workflows')" wire:navigate>{{ __('WPI Workflow Administration') }}</flux:navlist-horizontal.item>
            </flux:navlist-horizontal>
        </div>
         <div class="w-full md:hidden">
        <flux:navlist>
            <flux:navlist-horizontal.item :href="route('hazard.workflows')" wire:navigate>{{ __('Hazard Workflow Administration') }}</flux:navlist-horizontal.item>
            <flux:navlist-horizontal.item :href="route('wpi.workflows')" wire:navigate>{{ __('WPI Workflow Administration') }}</flux:navlist-horizontal.item>

        </flux:navlist>
    </div>
    </div>
    <div class="py-2 ">
        {{-- <flux:heading>{{ $heading ?? '' }}</flux:heading> --}}
        <flux:subheading class="mb-2" size='xs'>{{ $subheading ?? '' }}</flux:subheading>

        @if ($activeTab === 'hazard')
            <x-tabs-workflow-event.panel_hazard />
        @endif

        <div
            class="flex w-full flex-1 flex-col gap-4 rounded-xl inset-shadow-sm h-full max-h-[calc(100vh-16rem)] sm:max-h-[calc(100vh-16rem)] md:max-h-[calc(100vh-14rem)] lg:max-h-[calc(100vh-14rem)] 2xl:max-h-[calc(100vh-14rem)]">
            <div
                class="flex-1 h-full p-4 overflow-x-hidden overflow-y-auto border rounded-xl border-neutral-200 dark:border-base-200">
                <div class="w-full max-w-full ">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
