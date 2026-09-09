@props([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'placeholder' => null,
    'invalid' => null,
    'size' => null,
])

@php
    $invalid ??= $name && $errors->has($name);

    $classes = Flux::classes()
        ->add('appearance-none ') // Strip the browser's default <select> styles...
    ->add('w-full ps-3 pe-10 block ')
    ->add(
        match ($size) {
            default => 'h-10 py-2 text-base sm:text-sm leading-[1.375rem] rounded-lg',
            'sm' => 'h-8 py-1.5 text-sm leading-[1.125rem] rounded-xs',
            'xs' => 'h-6 text-xs leading-[1.125rem] rounded-xs',
        },
    )
    ->add('shadow-xs  ')
    // --- BARIS INI DIMODIFIKASI UNTUK MENYESUAIKAN DENGAN BACKGROUND INPUT ---
    // Menggunakan kelas bg-base-100 (yang mungkin putih di light mode) dan dark:bg-transparent.
    ->add('bg-white dark:disabled:bg-white/[7%] dark:border') // Mengikuti dark:bg-transparent dari input 'outline'
    // -------------------------------------------------------------------------
    ->add('text-black dark:text-zinc-800 font-semibold') // Mengubah dark:text-zinc-700 menjadi dark:text-zinc-200 agar terlihat di background gelap
    // Make the placeholder match the text color of standard input placeholders...
    ->add('has-[option.placeholder:checked]:text-black dark:has-[option.placeholder:checked]:text-black')
    // Options on Windows don't inherit dark mode styles, so we need to force them...
        ->add('dark:[&>option]:bg-base-100 dark:[&>option]:text-base-content ')
        ->add('disabled:shadow-none')
        ->add($invalid ? 'border border-rose-500' : 'border border-zinc-200 border-black/10 focus:outline-none ');
@endphp

<select {{ $attributes->class($classes) }} @if ($invalid) aria-invalid="true" data-invalid @endif
    @isset($name) name="{{ $name }}" @endisset
    @if (is_numeric($size)) size="{{ $size }}" @endif data-flux-control data-flux-select-native
    data-flux-group-target>
    <?php if ($placeholder): ?>
    <option value="" selected class="placeholder">{{ $placeholder }}</option>
    <?php endif; ?>

    {{ $slot }}
</select>
