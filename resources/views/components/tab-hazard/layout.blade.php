<div class="z-30 flex items-start max-md:flex-col ">
    <div class="self-stretch flex-1 max-md:pt-3">
        <div class="flex w-full flex-1 flex-col gap-4 rounded-xl h-full
            max-h-[calc(100vh-20rem)]
            sm:max-h-[calc(100vh-11rem)]
            md:max-h-[calc(100vh-16rem)]
            lg:max-h-[calc(100vh-18rem)]
            2xl:max-h-[calc(100vh-18rem)]">
            <div class="flex-1 h-full px-4 py-2 overflow-x-hidden overflow-y-auto border inset-shadow-sm rounded-xl border-neutral-200 dark:border-base-200">
                <div class="w-full">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
