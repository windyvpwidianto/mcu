<div class="flex flex-col ">
     <div class=" hidden md:block w-60">
        <flux:navlist-horizontal>
            <flux:navlist-horizontal.item :href="route('administration-risk-Consequence')"    wire:navigate>{{ __('Risk Consequence') }}</flux:navlist-horizontal.item>
            <flux:navlist-horizontal.item :href="route('administration-risk-Likelihood')"    wire:navigate>{{ __('Risk Likelihood') }}</flux:navlist-horizontal.item>
            <flux:navlist-horizontal.item :href="route('administration-risk-Assessement')"    wire:navigate>{{ __('Risk Assessment') }}</flux:navlist-horizontal.item>
            <flux:navlist-horizontal.item :href="route('administration-risk-Matrix')"    wire:navigate>{{ __('Risk Matrix') }}</flux:navlist-horizontal.item>
        </flux:navlist-horizontal>
    </div>
     <div class=" md:hidden w-full">
        <flux:navlist>
            <flux:navlist-horizontal.item :href="route('administration-risk-Consequence')"    wire:navigate>{{ __('Risk Consequence') }}</flux:navlist-horizontal.item>
            <flux:navlist-horizontal.item :href="route('administration-risk-Likelihood')"    wire:navigate>{{ __('Risk Likelihood') }}</flux:navlist-horizontal.item>
            <flux:navlist-horizontal.item :href="route('administration-risk-Assessement')"    wire:navigate>{{ __('Risk Assessment') }}</flux:navlist-horizontal.item>
            <flux:navlist-horizontal.item :href="route('administration-risk-Matrix')"    wire:navigate>{{ __('Risk Matrix') }}</flux:navlist-horizontal.item>
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
