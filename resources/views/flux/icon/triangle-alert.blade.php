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


<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-triangle-alert-icon lucide-triangle-alert">
    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3" />
    <path d="M12 9v4" />
    <path d="M12 17h.01" /></svg>
