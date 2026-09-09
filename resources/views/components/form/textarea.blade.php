@props([
    'label' => null,
    'placeholder' => '',
    'model' => null,
    'rows' => 3,
    'size' => 'text-xs',
    'required' => false,
    'disabled' => false,
])
<div class="w-full">
    <label class="w-full mb-1 floating-label">
        <textarea
            {{ $model ? "wire:model.live=$model" : '' }}
            placeholder="{{ $placeholder ?: $label }}"
            rows="{{ $rows }}"
            @disabled($disabled) {{-- Gunakan direktif disabled --}}
            {{ $attributes->merge([
                'class' => "textarea textarea-bordered focus:border-info focus:ring-info focus:outline-hidden w-full $size border-gray-300 rounded " .
                ($errors->has($model) ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500 ' : '') .
                ($disabled ? 'bg-gray-100 cursor-not-allowed opacity-70' : '') {{-- Tambahkan styling visual --}}
            ]) }}
        ></textarea>
        @if ($label)
            <span>{{ $label }} @if ($required)<span class="font-bold text-red-500">*</span>@endif</span>
        @endif
    </label>

    {{-- Penanganan Error Otomatis --}}
    @if ($model)
        <x-label-error :messages="$errors->get($model)" />
    @endif
</div>
