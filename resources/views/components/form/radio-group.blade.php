@props([
'label',
'model',
'options' => [
['value' => 'Yes', 'label' => 'Yes', 'class' => 'radio-warning'],
['value' => 'No', 'label' => 'No', 'class' => 'radio-success'],
],
'required' => false,
'disabled' => false // Tambahkan props default disabled
])
<fieldset class="fieldset">
    @if($label)
    <x-form.label :label="$label" :required="$required" />
    @endif

    <div @class([ 'flex items-center border-2 gap-4 px-2  transition-colors' , 'bg-base-200/50 cursor-not-allowed opacity-70'=> $disabled, // Style saat disabled
        'border-error/50 bg-error/5' => $errors->has($model) && !$disabled,
        'border-base-300 focus-within:border-info' => !$errors->has($model) && !$disabled
        ])>
        @foreach($options as $option)
        <label @class([ 'flex items-center gap-2 group' , 'cursor-pointer'=> !$disabled,
            'cursor-not-allowed' => $disabled
            ])>
            <input
                type="radio"
                name="{{ $model }}"
                wire:model.live="{{ $model }}"
                value="{{ $option['value'] }}"
                {{ $disabled ? 'disabled' : '' }} {{-- Atribut disabled HTML --}}
                {{ $attributes->merge(['class' => 'radio radio-xs ' . ($option['class'] ?? 'radio-primary')]) }} />
            <span @class([ 'text-sm transition-colors' , 'group-hover:text-primary'=> !$disabled,
                'text-base-content/50' => $disabled
                ])>
                {{ __($option['label']) }}
            </span>
        </label>
        @endforeach
    </div>

    @error($model)
    <x-label-error :messages="$errors->get($model)" />
    @enderror
</fieldset>