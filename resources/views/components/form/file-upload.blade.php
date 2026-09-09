@props([
'label' => 'Lampirkan foto atau dokumentasi',
'id' => 'upload-' . md5($model ?? uniqid()),
'model' => null,
'existingFile' => null,
'newFile' => null,
'isDisabled' => false,
'optional' => true,
])

@php
$fileToPreview = $newFile ?? ($existingFile ? (object) ['name' => $existingFile] : null);
$fileName = null;
$extension = null;
$isImage = false;
$fileUrl = null;

if ($fileToPreview) {
$fileName = $newFile instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
? $newFile->getClientOriginalName()
: (is_object($fileToPreview) ? $fileToPreview->name : $existingFile);

$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
$previewableExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];

if ($newFile) {
if (in_array($extension, $previewableExtensions) && method_exists($newFile, 'temporaryUrl')) {
try {
$fileUrl = $newFile->temporaryUrl();
} catch (\Exception $e) { $fileUrl = null; }
}
} else {
$fileUrl = $existingFile ? asset('storage/' . $existingFile) : null;
}
}
@endphp

<fieldset {{ $attributes->merge(['class' => 'fieldset']) }}>
    {{-- Label Dinamis --}}
    <x-form.label :label="__($label) . ($optional ? ' (' . __('optional') . ')' : '')" />

    <label for="{{ $id }}"
        class="flex items-center gap-2 {{ $isDisabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }} border border-info rounded hover:ring-1 hover:border-info hover:ring-info hover:outline-hidden transition-all">

        <span class="btn btn-info btn-xs {{ $isDisabled ? 'btn-disabled' : '' }}">
            {{ __('Pilih file atau gambar') }}
        </span>

        {{-- Loading State --}}
        <div class="hidden" wire:loading.remove.class='hidden' wire:target="{{ $model }}">
            <span class="flex items-center gap-1 px-2">
                <span class="loading loading-bars loading-xs text-info"></span>
                <span class="text-xs text-info">{{ __('Mengunggah...') }}</span>
            </span>
        </div>

        <span wire:loading.remove wire:target="{{ $model }}" class="text-[9px] text-gray-500 truncate max-w-sm">
            @if ($newFile)
            {{ $newFile->getClientOriginalName() }}
            @elseif($existingFile)
            {{ basename($existingFile) }}
            @else
            {{ __('Belum ada file') }}
            @endif
        </span>
    </label>

    {{-- Preview Area --}}
    <div class="mt-2" wire:loading.remove wire:target="{{ $model }}">
        @if ($fileToPreview)
        <div class="text-[10px] font-medium {{ $newFile ? 'text-green-600' : 'text-gray-600' }}">
            {{ $newFile ? __('Preview file baru:') : __('File lama:') }}
        </div>

        @if ($isImage && $fileUrl)
        <div class="relative inline-block">
            <img src="{{ $fileUrl }}" class="object-cover h-24 mt-1 border rounded shadow-sm">
            <a href="{{ $fileUrl }}" target="_blank"
                class="block text-[10px] text-blue-500 hover:underline mt-1">
                {{ __('Lihat Fullscreen') }}
            </a>
        </div>
        @else
        <div class="flex items-center gap-2 mt-1">
            {{-- Icon logic remains the same --}}
            @if ($extension == 'pdf') <x-icon.pdf class="w-8 h-8" />
            @elseif (in_array($extension, ['doc', 'docx'])) <x-icon.word class="w-8 h-8" />
            @elseif (in_array($extension, ['xlsx', 'xls', 'csv', 'xlsm'])) <x-icon.excel class="w-8 h-8" />
            @else
            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zM6 20V4h7v4h4v12H6z" />
            </svg>
            @endif

            <div class="flex flex-col">
                <span class="text-xs font-semibold truncate max-w-[200px] {{ $extension == 'pdf' ? 'text-red-600' : '' }}">
                    {{ $fileName }}
                </span>

                @if ($fileUrl)
                <a href="{{ $fileUrl }}" target="_blank" class="text-[10px] text-blue-500 hover:underline">
                    {{ $newFile ? __('Pratinjau') : __('Download / Lihat') }}
                </a>
                @endif
            </div>
        </div>
        @endif
        @endif
    </div>

    <input type="file" id="{{ $id }}" {{ $isDisabled ? 'disabled' : '' }} wire:model="{{ $model }}" class="hidden" />

    @if ($model)
    @error($model)
    <span class="text-xs text-red-500">{{ $message }}</span>
    @enderror
    @endif
</fieldset>