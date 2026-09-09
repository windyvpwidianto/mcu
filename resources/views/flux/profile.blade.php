@php $iconVariant = $iconVariant ??= $attributes->pluck('icon:variant'); @endphp
@php $iconTrailing = $iconTrailing ??= $attributes->pluck('icon:trailing'); @endphp

@props([
'iconVariant' => 'micro',
'iconTrailing' => null,
'initials' => null,
'chevron' => true,
'circle' => null,
'avatar' => null,
'name' => null,
])

@php
$iconTrailing = $iconTrailing ?? ($chevron ? 'chevron-down' : null);

$initials ??= collect(explode(' ', $name ?? ''))
->map(fn($part) => Str::substr($part, 0, 1))
->filter()
->only([0, count(explode(' ', $name ?? '')) - 1])
->implode('');

// Warna Icon: Menggunakan base-content dengan opasitas (seperti debu mineral)
// Saat di-hover, icon akan menyala menjadi warna emas (primary)
$iconClasses = Flux::classes('text-[var(--color-base-content)]/60 group-hover:text-[var(--color-primary)] transition-colors duration-200')
->add($iconVariant === 'outline' ? 'size-4' : '');

$classes = Flux::classes()
->add('group flex items-center transition-all duration-200')
->add('rounded-lg has-data-[circle=true]:rounded-full')
->add('[ui-dropdown>&]:w-full')
/* Hover state: Menggunakan background tipis dari base-content */
->add('p-1 hover:bg-[var(--color-base-content)]/5 border border-transparent hover:border-[var(--color-primary)]/10')
;
@endphp

<button type="button" {{ $attributes->class($classes) }} data-flux-profile>
    <div class="shrink-0">
        <?php if ($avatar instanceof \Illuminate\View\ComponentSlot): ?>
            {{ $avatar }}
        <?php else: ?>
            {{-- Avatar akan mewarisi warna tema melalui props yang dikirim --}}
            <flux:avatar :attributes="Flux::attributesAfter('avatar:', $attributes, [
                'src' => $avatar,
                'size' => 'xs',
                'circle' => $circle,
                'name' => $name,
                'initials' => $initials,
                'class' => 'bg-[var(--color-base-300)] text-[var(--color-base-content)] border border-[var(--color-primary)]/20'
            ])" />
        <?php endif; ?>
    </div>

    <?php if ($name): ?>
        {{-- Nama: Menggunakan warna base-content (Arang/Kapur) dan berubah menjadi Emas saat hover --}}
        <span class="mx-2 text-xs text-[var(--color-base-content)]/80 group-hover:text-[var(--color-primary)] font-semibold capitalize truncate transition-colors duration-200">
            {{ $name }}
        </span>
    <?php endif; ?>

    <?php if (is_string($iconTrailing) && $iconTrailing !== ''): ?>
        <div class="flex items-center justify-center shrink-0 ms-auto size-8">
            <flux:icon :icon="$iconTrailing" :variant="$iconVariant" :class="$iconClasses" />
        </div>
    <?php elseif ($iconTrailing): ?>
        {{ $iconTrailing }}
    <?php endif; ?>
</button>
