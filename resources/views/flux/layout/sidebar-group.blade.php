@props(['title'])

<div class="mt-4">
    <h3 class="px-4 py-2 text-gray-400 uppercase text-xs tracking-wider">{{ $title }}</h3>
    <div class="flex flex-col">
        {{ $slot }}
    </div>
</div>
