@php
$classes = Flux::classes()
    ->add('flex group/button-group') // Mengubah nama grup agar lebih spesifik
    ->add('isolate') // Memastikan elemen yang di-hover/focus naik ke atas border lainnya
    ->add([
        // Mengatur warna border default untuk semua anak grup agar konsisten dengan tema tambang
        '[&_[data-flux-group-target]]:border-[var(--color-base-300)]',

        // Efek hover kolektif: saat grup di-hover, border sedikit lebih tegas
        'hover:[&_[data-flux-group-target]]:border-[var(--color-base-content)]/20',

        // Logic penggabungan border (Welding borders)
        // Menghapus border kiri untuk elemen tengah dan terakhir agar tidak double border
        '[&>[data-flux-input]:last-child:not(:first-child)>[data-flux-group-target]:not([data-invalid])]:border-s-0',
        '[&>[data-flux-input]:not(:first-child):not(:last-child)>[data-flux-group-target]:not([data-invalid])]:border-s-0',
        '[&>[data-flux-input]:has(+[data-flux-input-group-suffix])>[data-flux-group-target]:not([data-invalid])]:border-e-0',

        // Selects and date pickers borders
        '[&>*:last-child:not(:first-child)>[data-flux-group-target]:not([data-invalid])]:border-s-0',
        '[&>*:not(:first-child):not(:last-child)>[data-flux-group-target]:not([data-invalid])]:border-s-0',
        '[&>*:has(+[data-flux-input-group-suffix])>[data-flux-group-target]:not([data-invalid])]:border-e-0',

        // Buttons borders
        '[&>[data-flux-group-target]:last-child:not(:first-child)]:border-s-0',
        '[&>[data-flux-group-target]:not(:first-child):not(:last-child)]:border-s-0',
        '[&>[data-flux-group-target]:has(+[data-flux-input-group-suffix])]:border-e-0',

        // Radius Management: Mengikuti --radius-field yang kita set di tema (0.125rem)
        // Menghilangkan radius di sisi yang berhimpit agar terlihat menyatu seperti balok logam
        '[&>[data-flux-group-target]:not(:first-child):not(:last-child)]:rounded-none',
        '[&>[data-flux-group-target]:first-child:not(:last-child)]:rounded-e-none',
        '[&>[data-flux-group-target]:last-child:not(:first-child)]:rounded-s-none',

        // Weld borders for sub-children
        '[&>*:not(:first-child):not(:last-child):not(:only-child)>[data-flux-group-target]]:rounded-none',
        '[&>*:first-child:not(:last-child)>[data-flux-group-target]]:rounded-e-none',
        '[&>*:last-child:not(:first-child)>[data-flux-group-target]]:rounded-s-none',

        // Weld borders for sub-sub-children (Comboboxes)
        '[&>*:not(:first-child):not(:last-child):not(:only-child)>[data-flux-input]>[data-flux-group-target]]:rounded-none',
        '[&>*:first-child:not(:last-child)>[data-flux-input]>[data-flux-group-target]]:rounded-e-none',
        '[&>*:last-child:not(:first-child)>[data-flux-input]>[data-flux-group-target]]:rounded-s-none',
    ])
    ;
@endphp

<div {{ $attributes->class($classes) }} data-flux-button-group>
    {{ $slot }}
</div>
