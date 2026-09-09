@props(['href' => '#', 'icon' => null, 'submenu' => null])

<div x-data="{ open: false }">
    <a href="{{ $href }}"
       @click.prevent="submenu ? open = !open : null"
       class="flex items-center justify-between px-4 py-2 text-red-200 hover:bg-gray-200 rounded transition-colors duration-200 cursor-pointer"
    >
        <div class="flex items-center space-x-2">
            @if($icon)
                <span>{!! $icon !!}</span>
            @endif
            <span x-show="!$parent.collapsed">{{ $slot }}</span>
        </div>

        @if($submenu)
            <svg :class="open ? 'rotate-90' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5l7 7-7 7"/>
            </svg>
        @endif
    </a>

    {{-- Submenu --}}
    @if($submenu)
        <div x-show="open" x-transition class="ml-6 flex flex-col space-y-1 mt-1">
            {{ $submenu }}
        </div>
    @endif
</div>
