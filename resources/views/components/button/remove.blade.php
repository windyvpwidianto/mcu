@props([
    'click' => null,
    'key' => null,
    'confirm' => null,
])

<label
    @if($click) wire:click="{{ $click }}" @endif
    @if($key) wire:key="{{ $key }}" @endif
    @if($confirm) wire:confirm="{{ $confirm }}" @endif
    {{ $attributes->merge([
        'class' => 'absolute cursor-pointer -top-2 -right-2 btn btn-circle btn-xs btn-ghost hover:bg-rose-500 hover:text-white'
    ]) }}
>
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-badge-x">
        <path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
        <line x1="15" x2="9" y1="9" y2="15" />
        <line x1="9" x2="15" y1="9" y2="15" />
    </svg>
</label>
