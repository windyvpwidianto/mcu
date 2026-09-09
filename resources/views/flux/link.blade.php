@props([
    'external' => null,
    'accent' => true,
    'variant' => null,
    'strong' => false,
])

@php
$classes = Flux::classes()
    ->add('inline font-medium transition-colors duration-200')
    ->add('underline-offset-[6px] hover:decoration-current')
    ->add(match ($variant) {
        'ghost' => 'no-underline hover:underline',
        'subtle' => 'no-underline',
        default => 'underline',
    })
    /* Mengatur dekorasi garis bawah agar menggunakan opasitas dari variabel warna tema */
    ->add('[[data-color]>&]:text-inherit [[data-color]>&]:decoration-current/20 [[data-color]>&]:hover:decoration-current')
    ->add(match ($variant) {
        /* Variant Subtle: Menggunakan warna teks konten yang diredupkan */
        'subtle' => 'text-[var(--color-base-content)]/60 hover:text-[var(--color-base-content)]',

        default => match ($accent) {
            /* Accent True: Menggunakan warna EMAS (Primary) untuk link agar terlihat mewah */
            true => 'text-[var(--color-primary)] decoration-[var(--color-primary)]/30 hover:text-[var(--color-primary)]/80',

            /* Accent False: Menggunakan warna teks standar tema (Base Content) */
            false => 'text-[var(--color-base-content)] decoration-[var(--color-base-content)]/20',
        },
    })
    /* Jika 'strong' aktif, kita buat teks sedikit lebih tebal dan cerah */
    ->add($strong ? 'font-bold' : '')
    ;
@endphp
{{-- NOTE: It's important that this file has NO newline at the end of the file. --}}
<a {{ $attributes->class($classes) }} data-flux-link <?php if ($external) : ?>target="_blank"<?php endif; ?>>{{ $slot }}</a>
