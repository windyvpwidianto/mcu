@props([
'label' => null,
'required' => false,
'model' => null,
'options' => [],
'optionValue' => 'id',
'optionLabel' => 'name',
'placeholder' => '-- Pilih --'
])
<fieldset class="fieldset "> {{-- Tambahkan p-0 agar tidak merusak spacing --}}
    @if($label)
    <x-form.label :label="$label" :required="$required" />
    @endif
    <select {{ $model ? "wire:model.live=$model" : '' }} {{ $attributes->merge([
        'class' => 'select select-xs select-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 ' .
        ($errors->has($model) ? 'border-rose-500 ring-rose-500 focus:border-rose-500 focus:ring-0' : '')
    ]) }}>
        <option value="">{{ __($placeholder) }}</option>

        @foreach($options as $key => $option)
        @php
        if (is_object($option)) {
        // Jika data dari Eloquent Collection
        $value = $option->$optionValue;
        $displayLabel = __($option->$optionLabel);
        } elseif (is_array($option)) {
        // Jika data dari toArray() atau pluck()
        // Mengambil berdasarkan key string 'id' dan 'name'
        $value = $option[$optionValue] ?? $key;
        $displayLabel = __($option[$optionLabel] ?? (is_string($option) ? $option : $key));
        } else {
        // Jika data array sederhana [1 => 'Nama']
        $value = $key;
        $displayLabel = __($option);
        }
        @endphp
        <option value="{{ $value }}">
            {{ __($displayLabel) }}
        </option>
        @endforeach
    </select>

    @if($model)
    <x-label-error :messages="$errors->get($model)" />
    @endif
</fieldset>
