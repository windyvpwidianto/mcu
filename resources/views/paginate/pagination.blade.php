@if ($paginator->hasPages())
    <div class="flex flex-col sm:flex-row items-center justify-between px-4 py-3 gap-4 border-t border-[var(--color-base-300)] mt-4">

        {{-- Info text: Menggunakan warna base-content dengan opasitas agar tidak terlalu kontras --}}
        <div class="text-xs text-[var(--color-base-content)]/60">
            Showing
            <span class="font-bold text-[var(--color-base-content)]">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-bold text-[var(--color-base-content)]">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-bold text-[var(--color-base-content)]">{{ $paginator->total() }}</span>
            results
        </div>

        {{-- Pagination Group: Menggunakan DaisyUI Join --}}
        <div class="shadow-sm join">
            {{-- Previous Page --}}
            @if ($paginator->onFirstPage())
                <button class="btn btn-xs join-item bg-[var(--color-base-200)] border-[var(--color-base-300)] opacity-40 cursor-not-allowed">«</button>
            @else
                <button wire:click="previousPage" type="button" class="btn btn-xs join-item bg-[var(--color-base-200)] border-[var(--color-base-300)] text-[var(--color-base-content)] hover:bg-[var(--color-primary)] hover:text-[var(--color-primary-content)] transition-colors">«</button>
            @endif

            {{-- Page Numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <button class="btn btn-xs join-item bg-[var(--color-base-200)] border-[var(--color-base-300)] opacity-50 cursor-default">{{ $element }}</button>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            {{-- Active Page: Full Gold --}}
                            <button class="btn btn-xs join-item btn-primary no-animation">{{ $page }}</button>
                        @else
                            {{-- Inactive Pages --}}
                            <button wire:click="gotoPage({{ $page }})" type="button" class="btn btn-xs join-item bg-[var(--color-base-100)] border-[var(--color-base-300)] text-[var(--color-base-content)]/70 hover:bg-[var(--color-base-300)] hover:text-[var(--color-base-content)] transition-all">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" type="button" class="btn btn-xs join-item bg-[var(--color-base-200)] border-[var(--color-base-300)] text-[var(--color-base-content)] hover:bg-[var(--color-primary)] hover:text-[var(--color-primary-content)] transition-colors">»</button>
            @else
                <button class="btn btn-xs join-item bg-[var(--color-base-200)] border-[var(--color-base-300)] opacity-40 cursor-not-allowed">»</button>
            @endif
        </div>
    </div>
@endif
