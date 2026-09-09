@props([
    'name',
    'label' => null,
    'required' => false,
])
<fieldset class="fieldset">
     @if($label)
        <x-form.label :label="$label" :required="$required" />
    @endif

    <label wire:ignore for="upload-{{ $name }}" 
        class="flex items-center gap-2 cursor-pointer border border-info rounded 
               hover:ring-1 hover:border-info hover:ring-info hover:outline-hidden ">
        <!-- Tombol custom -->
        <span class="btn btn-info btn-xs">Pilih file atau gambar</span>
        <!-- Nama file -->
        <span id="file-name-{{ $name }}" class="text-xs text-gray-500">Belum ada file</span>
    </label>

    @if (${$name} ?? false)
        @php
            $file = ${$name};
            $ext = strtolower($file->getClientOriginalExtension());
        @endphp

        @if (in_array($ext, ['jpg','jpeg','png','gif','webp']))
            <img src="{{ $file->temporaryUrl() }}" class="mt-2 w-40 h-auto rounded border" />
        @else
            <p class="mt-2 text-xs text-gray-600">File: {{ $file->getClientOriginalName() }}</p>
        @endif
    @endif

    <!-- Input asli -->
    <input id="upload-{{ $name }}" 
           name="{{ $name }}" 
           type="file" 
           wire:model.live="{{ $name }}" 
           class="hidden"
           onchange="document.getElementById('file-name-{{ $name }}').textContent = this.files[0]?.name ?? 'Belum ada file'">

    <x-label-error :messages="$errors->get($name)" />
</fieldset>
