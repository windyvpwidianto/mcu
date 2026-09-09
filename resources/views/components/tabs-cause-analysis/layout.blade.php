<div class="flex  md:flex-col ">
    <div class=" w-full  md:w-60 ">
        <flux:navlist-horizontal>
            <flux:navlist-horizontal.item :href="route('kta')"    wire:navigate>{{ __('Kondisi Tidak Aman') }}</flux:navlist-horizontal.item>
            <flux:navlist-horizontal.item :href="route('tta')"   wire:navigate>{{ __('Tindakkan Tidak Aman') }}</flux:navlist-horizontal.item>
        </flux:navlist-horizontal>
    </div>
    {{-- <flux:separator class="md:hidden" /> --}}
    <div class=" p-2 ">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading size='xs'>{{ $subheading ?? '' }}</flux:subheading>
        <div class="flex w-full flex-1 flex-col gap-4 rounded-xl 
            h-[calc(100vh-6rem)] 
            sm:max-h-[calc(100vh-8rem)] 
            md:max-h-[calc(100vh-11rem)] 
            lg:max-h-[calc(100vh-13rem)] 
            2xl:max-h-[44rem]">
            <div class="h-full flex-1 overflow-y-auto overflow-x-hidden rounded-xl border border-neutral-200 dark:border-base-200 p-4">
                <div class="w-full max-w-full break-words">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>