@props(['collapsed' => false])

<div
    x-data="{
        collapsed: {{ $collapsed ? 'true' : 'false' }},
        hidden: false,
        screenLg: window.innerWidth >= 1024,
        toggleCollapse() { this.collapsed = !this.collapsed },
        toggleHide() { this.hidden = !this.hidden }
    }"
    x-init="window.addEventListener('resize', () => { screenLg = window.innerWidth >= 1024 })"
    :class="hidden ? 'w-0' : (collapsed ? 'w-20' : 'w-64')"
    class="flex flex-col h-screen bg-base-300 border-r border-gray-200 transition-all duration-300 overflow-hidden"
>
    {{-- Header --}}
    <div class="flex items-center justify-between p-4 border-b border-gray-200 sticky top-0 bg-base-300 z-10">
        <span class="font-bold text-lg transition-all duration-300" x-show="!collapsed && !hidden">My App</span>
        <div class="flex gap-2">
            {{-- Tombol collapse --}}
            {{-- <button @click="toggleCollapse()" class="p-1 rounded hover:bg-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button> --}}
            {{-- Tombol hide --}}
            <button @click="toggleHide()" class="p-1 rounded hover:bg-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Content --}}
    <div class="flex-1 overflow-y-auto" x-show="!hidden">
        {{ $slot }}
    </div>

    {{-- Footer --}}
    <div class="p-4 border-t border-gray-200 text-sm text-gray-500 sticky bottom-0 bg-base-300 z-10" x-show="!collapsed && !hidden">
        Footer Content
    </div>
</div>
