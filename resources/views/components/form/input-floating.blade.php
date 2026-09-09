@props([
'label' => null,
'type' => 'text',
'required' => false,
'placeholder' => '',
'model' => null,
'size' => 'input-xs', // default size sesuai permintaan Anda
])
<fieldset class="fieldset">
    <label {{ $attributes->merge(['class' => 'floating-label w-full']) }}>
        <input type="{{ $type }}" placeholder="{{ (__($placeholder)) ?: (__($label)) }}"
            {{ $model ? "wire:model.live=$model" : '' }} {{ $attributes->whereDoesntStartWith('class') }}
            class="input input-bordered {{ $size }} w-full focus:border-info focus:ring-info focus:outline-hidden
            border-gray-300 rounded
            {{ $errors->has($model) ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
        @if ($label)
        <span>{{ $label }} @if ($required)<span class="font-bold text-red-500">*</span>@endif</span>
        @endif
    </label>

    {{-- Tampilkan Error jika menggunakan WireModel --}}
    @if ($model)
    @error($model)
    <x-label-error :messages="$errors->get($model)" />
    @enderror
    @endif
</fieldset>