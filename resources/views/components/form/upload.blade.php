@props([
'label' => 'Lampirkan foto atau dokumentasi',
'id' => 'upload-' . md5($attributes->get('wire:model') ?? uniqid()),
'model' => null,
'file' => null,
'title' => 'Pilih file atau gambar',
'keterangan' => 'Tidak ada file yang dipilih',
'optional' => true,
'disabled' => false,
'required' => false,
'multiple' => false, // Tambahkan prop multiple
])

<fieldset class="fieldset">
    <x-form.label :label="__($label) . ($optional ? ' (' . __('optional') . ')' : '')" :required="$required" />

    <label for="{{ $disabled ? '' : $id }}"
        @class([ 'flex items-center gap-2 border rounded-full border-info' , 'cursor-pointer hover:ring-1 hover:border-info hover:ring-info hover:outline-hidden'=> !$disabled,
        'cursor-not-allowed bg-gray-100 opacity-60 border-gray-300' => $disabled,
        'border-rose-500 ring-rose-500 focus:border-rose-500 focus:ring-0'=>$errors->has($model) || $errors->has($model . '.*')
        ])>

        <span @class([ 'btn btn-xs' , 'btn-info'=> !$disabled,
            'btn-disabled bg-gray-300 text-gray-500 border-none' => $disabled,
            'btn-disabled bg-error text-error-content border-none' => $errors->has($model)|| $errors->has($model . '.*')
            ])>
            {{ __($title) }}
        </span>

        {{-- Loading State --}}
        <span class="hidden" wire:loading.remove.class='hidden' wire:target="{{ $model }}">
            <span class="flex items-center gap-1 px-2">
                <span class="loading loading-bars loading-xs text-info"></span>
                <span class="text-xs text-info">{{ __('Mengunggah...') }}</span>
            </span>
        </span>

        {{-- File Name State (Logic Updated for Multiple) --}}
        <span wire:loading.remove wire:target="{{ $model }}" class="px-2 text-xs text-gray-500 truncate">
            @if ($multiple && is_array($file) && count($file) > 0)
            {{ count($file) }} {{ __('file terpilih') }}
            @elseif (!$multiple && $file && is_object($file))
            {{ $file->getClientOriginalName() }}
            @elseif ($file && is_string($file))
            {{ basename($file) }}
            @else
            {{ __($keterangan) }}
            @endif
        </span>
    </label>

    <input
        type="file"
        id="{{ $id }}"
        wire:model="{{ $model }}"

        {{ $attributes->merge([
        'accept' => '*',
        'multiple' => $multiple,
        'disabled' => $disabled
    ])->except(['wire:model', 'wire:model.live', 'wire:model.blur']) }}

        class="hidden" />

    @error($model . '.*')
    <x-label-error :messages="$errors->get($model)" />
    @enderror

    {{-- Tambahkan juga error untuk field utamanya (misal jika array kosong) --}}
    @error($model)
    <x-label-error :messages="$errors->get($model)" />
    @enderror
</fieldset>
