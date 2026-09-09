@props([
'label' => null,
'placeholder' => '',
'model' => null,
'type' => 'text',
'size' => 'input-xs',
'required' => false,
'disabled' => false,
])

<fieldset class="w-full fieldset">
    {{-- Label dengan indikator required --}}
    @if ($label)
    <x-form.label :label="$label" :required="$required" />
    @endif

    {{-- Input Element --}}
    <input {{ $disabled ? 'disabled' : '' }}
        type="{{ $type }}"
        {{ $model ? "wire:model.live=$model" : '' }}
        placeholder="{{ (__($placeholder)) ?: (__($label)) }}"
        {{ $attributes->merge([
        'class' => "input input-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 $size " .
        ($errors->has($model)
            ? 'input-bordered border-rose-500 ring-rose-500 focus:border-rose-500 focus:ring-0' // Jika Error
            : 'input-bordered' // Jika Normal
        )
    ]) }} />

    {{-- Penanganan Error Otomatis --}}
    @if($model)
    <x-label-error :messages="$errors->get($model)" />
    @endif
</fieldset>