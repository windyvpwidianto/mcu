<div class="flex items-center">
    <div class="flex items-stretch join">
        <div class="flex items-center justify-center px-3 border border-r-0 border-base-300 join-item bg-base-200">
            @if ($lang === 'en')
            <span class="fi fi-gb"></span>
            @elseif ($lang === 'id')
            <span class="fi fi-id"></span>
            @else
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-languages">
                <path d="m5 8 6 6" />
                <path d="m4 14 6-6 2-3" />
                <path d="M2 5h12" />
                <path d="M7 2h1" />
                <path d="m22 22-5-10-5 10" />
                <path d="M14 18h6" />
            </svg>
            @endif
        </div>

        <select
            wire:model.live="lang"
            class="rounded-l-none select select-bordered select-sm focus:outline-none focus:border-info join-item">
            <option value="">{{ __('Pilih Bahasa') }}</option>
            <option value="en">English</option>
            <option value="id">Bahasa Indonesia</option>
        </select>
    </div>
</div>