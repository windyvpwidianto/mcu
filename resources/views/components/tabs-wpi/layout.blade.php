    <div class="z-30 flex max-md:flex-col ">
        <div class="self-stretch flex-1 max-md:pt-4">
            <div wire:ignore class="flex flex-col items-center justify-between gap-4 mb-2 md:mb-6 md:flex-row">
                <div>
                    <flux:heading>{{ $heading ?? '' }}</flux:heading>
                    <flux:subheading size='sm'>{{ $subheading ?? '' }}</flux:subheading>
                </div>
                <div class="flex items-center gap-3 ">
                    @if (Route::is('wpi.list'))
                    <a href="{{ route('wpi.create') }}" class="text-xs uppercase btn btn-primary btn-xs btn-soft">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-plus-icon lucide-plus">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg> Laporan Baru
                    </a>
                    @endif

                </div>
            </div>
            <div class="flex w-full flex-1 flex-col gap-4 rounded-xl inset-shadow-sm
                    max-h-[calc(100svh-26rem)]
                    sm:max-h-[calc(100svh-16rem)]
                    md:max-h-[calc(100svh-10rem)]
                    lg:max-h-[calc(100svh-10rem)]
                    2xl:max-h-[calc(100svh-10rem)]
                    relative">
                <div
                    class="flex-1 h-full p-4 overflow-x-hidden overflow-y-auto border rounded-xl border-neutral-200 dark:border-base-200">
                    <div class="w-full max-w-full ">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>