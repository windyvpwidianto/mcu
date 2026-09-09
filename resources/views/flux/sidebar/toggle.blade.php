@props(['icon' => 'bars-3'])

<button
    type="button"
    {{ $attributes->class('btn btn-xs btn-square btn-ghost') }}
    @click="
        if (document.body.hasAttribute('data-show-stashed-sidebar')) {
            document.body.removeAttribute('data-show-stashed-sidebar')
        } else {
            document.body.setAttribute('data-show-stashed-sidebar', true)
        }
    "
>
    <flux:icon :name="$icon" class="h-6 w-6" />
</button>
