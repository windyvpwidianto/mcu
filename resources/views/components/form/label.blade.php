@props([
'label',
'deskripsi' => false,
'deskripsi_value' => ''
])

<label {{ $attributes->merge(['class' => 'text-xs font-medium capitalize text-base-content']) }}>
    {{ __($label) }}

    @if ($required)
    <span class="font-bold text-red-500 ml-0.5">
        @if ($deskripsi)
        <span class="text-[10px] font-normal italic mr-0.5">
            {{ __($deskripsi_value) }}
        </span>
        @endif
        <sup class="text-[10px]">*</sup>
    </span>
    @endif
</label>