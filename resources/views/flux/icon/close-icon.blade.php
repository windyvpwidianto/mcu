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

<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-x-icon lucide-square-x">
    <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
    <path d="m15 9-6 6" />
    <path d="m9 9 6 6" /></svg>
