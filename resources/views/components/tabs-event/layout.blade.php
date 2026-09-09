<div>
    @if (auth()->User()->hasRole(['Administrator']))
    <div class="flex  flex-col ">
        <div class=" overflow-x-auto w-auto md:overflow-x-hidden ">
            <flux:navlist-horizontal>
                <flux:navlist-horizontal.item :href="route('administration-event_general-eventCategory')" wire:navigate>{{ __('Event Category') }}</flux:navlist-horizontal.item>
                <flux:navlist-horizontal.item :href="route('administration-event_general-eventType')" wire:navigate>{{ __('Event Type') }}</flux:navlist-horizontal.item>
                <flux:navlist-horizontal.item :href="route('administration-event_general-eventSubType')" wire:navigate>{{ __('Sub Event Type') }}</flux:navlist-horizontal.item>
                <flux:navlist-horizontal.item :href="route('administration-event_general-ModeratorAssignmentManager')" wire:navigate>{{ __('Moderator Assigment') }}</flux:navlist-horizontal.item>
                <flux:navlist-horizontal.item :href="route('administration-event_general-ErmAssignmentManager')" wire:navigate>{{ __('ERM Assigment') }}</flux:navlist-horizontal.item>
            </flux:navlist-horizontal>
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
    @else
    <div class="z-30 flex items-start max-md:flex-col ">
        <div class="self-stretch flex-1 max-md:pt-6">
            <flux:heading>{{ $heading ?? '' }}</flux:heading>
            <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
            <div class="flex w-full flex-1 flex-col gap-4 rounded-xl inset-shadow-sm h-full max-h-[calc(100vh-16rem)] sm:max-h-[calc(100vh-16rem)] md:max-h-[calc(100vh-14rem)] lg:max-h-[calc(100vh-14rem)] 2xl:max-h-[calc(100vh-14rem)] relative">
                <div class="flex-1 h-full p-4 overflow-x-hidden overflow-y-auto border rounded-xl border-neutral-200 dark:border-base-200">
                    <div class="w-full max-w-full ">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>