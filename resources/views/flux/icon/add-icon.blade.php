{{-- Credit: Lucide (https://lucide.dev) --}}

@props([
'variant' => 'outline',
])

@php
if ($variant === 'solid') {
throw new \Exception('The "solid" variant is not supported in Lucide.');
}

$classes = Flux::classes('shrink-0')->add(
match ($variant) {
'outline' => '[:where(&)]:size-6',
'solid' => '[:where(&)]:size-6',
'mini' => '[:where(&)]:size-5',
'micro' => '[:where(&)]:size-4',
},
);

$strokeWidth = match ($variant) {
'outline' => 2,
'mini' => 2.25,
'micro' => 2.5,
};
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-badge-plus-icon lucide-badge-plus">
    <path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
    <line x1="12" x2="12" y1="8" y2="16" />
    <line x1="8" x2="16" y1="12" y2="12" /></svg>
