@props([
    'name' => null,
    'message' => null,
    'nested' => true,
])

@php
$message ??= $name ? $errors->first($name) : null;

if ($name && (is_null($message) || $message === '') && filter_var($nested, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false) {
    $message = $errors->first($name . '.*');
}

$classes = Flux::classes('text-[9px] -mt-2 font-medium text-red-500 dark:text-red-400')
    ->add($message ? '' : 'hidden');
@endphp

<div role="alert" aria-live="polite" aria-atomic="true" {{ $attributes->class($classes) }} data-flux-error>
    <?php if ($message) : ?>
        <flux:icon icon="exclamation-triangle" variant="micro" class="inline" />

        {{ $message }}
    <?php endif; ?>
</div>
