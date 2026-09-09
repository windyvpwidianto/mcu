<fieldset class="mt-4 fieldset lg:col-span-2">
    <div class="flex items-center justify-between mb-1">
        <x-form.label label="Kunci Pembelajaran" required />

        {{-- Indikator Status di Pojok Kanan Label --}}
        @if(!$canEdit)
        <div class="gap-1 italic border-none opacity-50 badge badge-ghost badge-xs">
            <x-icon name="lock" class="w-2.5 h-2.5" /> Read Only
        </div>
        @endif
    </div>

    {{--
        CKEditor Helper:
        Pastikan di dalam ckeditorHelper, Anda menangkap parameter readonly.
    --}}
    <div x-data="ckeditorHelper('key_learning', {{ $canEdit ? 'false' : 'true' }})"
        wire:ignore
        wire:key="select-key-learning-{{ $canEdit ? 'edit' : 'view' }}">

        <div x-ref="editorElement"
            @class([ 'border rounded-lg overflow-hidden' , 'bg-base-200/50 cursor-not-allowed'=> !$canEdit,
            'border-base-300' => $canEdit
            ])
            data-placeholder="{{ $canEdit ? 'Masukkan kunci pembelajaran...' : 'Tidak ada data pembelajaran.' }}">
        </div>
    </div>

    <x-label-error :messages="$errors->get('key_learning')" />
</fieldset>