@props([
'modalId' => null,
'wireClick' => null,
'tooltip' => 'Tambah Data',
'color' => 'primary',
'icon' => 'add',
'href' => null, // Tambahkan prop href
'position' => 'top' // Default posisi di atas
])

@php
$colorClass = match($color) {
'secondary' => 'btn-secondary',
'accent' => 'btn-accent',
'info' => 'btn-info',
'success' => 'btn-success',
'warning' => 'btn-warning',
'error' => 'btn-error',
'default' => '',
default => 'btn-primary',
};

$tooltipColor = $color === 'default' ? '' : 'tooltip-' . $color;
/**
* Logika Responsif Tooltip Position
* Kita memecah string position (misal: "top md:right") menjadi array class DaisyUI
*/
$positions = explode(' ', $position);
$responsivePositionClasses = collect($positions)->map(function($pos) {
// Cek jika ada prefix (sm:, md:, lg:)
if (str_contains($pos, ':')) {
[$breakpoint, $actualPos] = explode(':', $pos);
return $breakpoint . ':tooltip-' . $actualPos;
}
return 'tooltip-' . $pos;
})->implode(' ');
// Logika menentukan tag yang digunakan
$tag = $href ? 'a' : 'button';
@endphp

{{-- Gabungkan class tooltip, warna, dan posisi di sini --}}
<div {{ $attributes->merge(['class' => "tooltip $tooltipColor $responsivePositionClasses"]) }}
    data-tip="{{ $tooltip }}">
    {{-- Custom Tooltip Content --}}
    <div class="z-[9999] tooltip-content ">
        <div class="text-sm font-black animate-bounce">{{ $tooltip }}</div>
    </div>

    <{{ $tag }}
        @if($href)
        href="{{ $href }}"
        @else
        type="button"
        onclick="{{ $modalId }}.showModal()"
        @endif

        @if($wireClick) wire:click="{{ $wireClick }}" @endif

        {{ $attributes->class(['btn btn-square btn-xs ', $colorClass]) }}>
        <x-dynamic-component :component="'icon.' . $icon" class="w-4 h-4" />
    </{{ $tag }}>
</div>