@props([
    'status',
])

@if ($status)
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        {{ $attributes->merge(['class' => 'relative flex items-center p-4 mb-4 text-sm rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-700 shadow-xs']) }}
        role="alert"
    >
        {{-- Ikon Check Circle --}}
        <svg class="flex-shrink-0 inline w-5 h-5 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
        </svg>

        <span class="sr-only">Success info</span>

        <div class="flex-1 pr-4">
            <span class="font-bold">{{ __('Berhasil!') }}</span> {{ $status }}
        </div>

        {{-- Tombol Close --}}
        <button
            @click="show = false"
            type="button"
            class="absolute top-0 right-0 p-2 m-1 transition-colors rounded-lg hover:bg-emerald-100 text-emerald-500"
            aria-label="Close"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif
