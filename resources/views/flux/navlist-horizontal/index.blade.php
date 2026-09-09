@props([
    'variant' => null,
])

@php
$classes = Flux::classes()
    ->add('flex flex-row')
    ->add('overflow-visible min-h-auto')
    ;
@endphp

<nav {{ $attributes->class($classes) }} data-flux-navlist>
    {{ $slot }}
</nav>
