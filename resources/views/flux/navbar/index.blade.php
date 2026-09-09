@props([
    'scrollable' => false,
    'variant' => null,
])

@php
$classes = Flux::classes()
    ->add('navbar')
    ->add($scrollable ? ['overflow-x-auto overflow-y-hidden'] : [])
    ;
@endphp

<nav {{ $attributes->class($classes) }} data-flux-navbar>
    {{ $slot }}
</nav>
