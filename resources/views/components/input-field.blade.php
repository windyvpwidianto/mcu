<fieldset class="fieldset">
    @if($label)
    <x-form.label :label="$label" :required="$required" />
    @endif

    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" {{ $attributes->merge([
            'class' =>
                'input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs ' .
                ($errors->has($name) ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '')
        ]) }} placeholder="{{ $placeholder }}" @if($wireModel) wire:model.live="{{ $wireModel }}" @endif />

    <x-label-error :messages="$errors->get($name)" />
</fieldset>
