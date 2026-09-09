<div class="w-full">
    {{-- 1. PROJECT MANAGER (KONTRAKTOR) --}}
    @if($contractor_id)
    <fieldset class="w-full p-4 mt-4 border fieldset bg-base-200 border-base-300 rounded-box">
        <legend class="flex items-center gap-2 fieldset-legend">
            {{ __('Penerimaan & Komentar Project Manager: (Jika kontraktor)') }}
            @if(!$canEdit) <x-icon name="lock" class="w-3 h-3 opacity-50" /> @endif
        </legend>

        <div x-data="ckeditorHelper('penerimaan_komentar_contractor', {{ $canEdit ? 'false' : 'true' }})"
            wire:ignore
            wire:key="ck-contractor-{{ $canEdit ? 'edit' : 'view' }}">
            <div x-ref="editorElement" data-placeholder="{{ __('Masukkan Penerimaan & Komentar Project Manager...') }}"></div>
        </div>
        <x-label-error :messages="$errors->get('penerimaan_komentar_contractor')" />

        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
            <x-form.searchable-select2 wire:key="select-penerimaan-komentar-contractor"
                placeholder="{{ __('Cari Nama Project Manager: (Jika kontraktor)') }}" modelsearch="searchNamePenerimaan.kontraktor"
                modelid="penerimaan_komentar_contractor_id"
                :options="$this->pelaporsPenerimaan"
                :showdropdown="$showPenerimaanKomentarContractorDropdown && $canEdit"
                clickaction="selectPenerimaanKomentarContractor(VALUE_ID, VALUE_NAME)"
                :disabled="!$canEdit"
                x-on:focusin="$canEdit ? $wire.set('activeTypePenerimaan', 'penerimaan_komentar_contractor') : null" />
        </div>
    </fieldset>
    @endif
    {{-- 2. PROJECT MANAGER (INTERNAL) --}}
    <fieldset class="w-full p-4 mt-2 border fieldset bg-base-200 border-base-300 rounded-box">
        <legend class="flex items-center gap-2 fieldset-legend">
            {{ __('Penerimaan & Komentar Project Manager: (Jika internal)') }}
            @if(!$canEdit) <x-icon name="lock" class="w-3 h-3 opacity-50" /> @endif
        </legend>

        <div x-data="ckeditorHelper('penerimaan_komentar_internal', {{ $canEdit ? 'false' : 'true' }})"
            wire:ignore
            wire:key="ck-internal-{{ $canEdit ? 'edit' : 'view' }}">
            <div x-ref="editorElement" data-placeholder="{{ __('Masukkan Penerimaan & Komentar Project Manager...') }}"></div>
        </div>
        <x-label-error :messages="$errors->get('penerimaan_komentar_internal')" />

        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
            <x-form.searchable-select2 wire:key="select-penerimaan-komentar-internal"
                placeholder="{{ __('Cari Nama Project Manager: (Jika internal)') }}" modelsearch="searchNamePenerimaan.internal"
                modelid="penerimaan_komentar_internal_id"
                :options="$this->pelaporsPenerimaan"
                :showdropdown="$showPenerimaanKomentarInternalDropdown && $canEdit"
                clickaction="selectPenerimaanKomentarInternal(VALUE_ID, VALUE_NAME)"
                :disabled="!$canEdit"
                x-on:focusin="$canEdit ? $wire.set('activeTypePenerimaan', 'penerimaan_komentar_internal') : null" />
        </div>
    </fieldset>

    {{-- 3. OHS DEPT HEAD --}}
    <fieldset class="w-full p-4 mt-2 border fieldset bg-base-200 border-base-300 rounded-box">
        <legend class="flex items-center gap-2 fieldset-legend">
            {{ __('Penerimaan & Komentar OHS Dept Head') }}
            @if(!$canEdit) <x-icon name="lock" class="w-3 h-3 opacity-50" /> @endif
        </legend>

        <div x-data="ckeditorHelper('penerimaan_komentar_ohs', {{ $canEdit ? 'false' : 'true' }})"
            wire:ignore
            wire:key="ck-ohs-{{ $canEdit ? 'edit' : 'view' }}">
            <div x-ref="editorElement" data-placeholder="{{ __('Masukkan Penerimaan & Komentar OHS Dept Head...') }}"></div>
        </div>
        <x-label-error :messages="$errors->get('penerimaan_komentar_ohs')" />

        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
            <x-form.searchable-select2 wire:key="select-penerimaan-komentar-ohs"
                placeholder="{{ __('Cari Nama OHS Dept Head...') }}" modelsearch="searchNamePenerimaan.ohs"
                modelid="penerimaan_komentar_ohs_id"
                :options="$this->pelaporsPenerimaan"
                :showdropdown="$showPenerimaanKomentarOhsDropdown && $canEdit"
                clickaction="selectPenerimaanKomentarOhs(VALUE_ID, VALUE_NAME)"
                :disabled="!$canEdit"
                x-on:focusin="$canEdit ? $wire.set('activeTypePenerimaan', 'penerimaan_komentar_ohs') : null" />
        </div>
    </fieldset>

    {{-- 4. KTT (Kondisional berdasarkan Rating) --}}
    @if(in_array($rating_name, ['Sedang', 'Tinggi', 'Ekstrem']))
    <fieldset wire:key="fieldset-ktt-{{ $consequence_id }}"
        class="w-full p-4 mt-2 border fieldset bg-base-200 border-base-300 rounded-box">
        <legend class="flex items-center gap-2 fieldset-legend">
            {{ __('Penerimaan & Komentar KTT: (Hanya untuk insiden dengan aktual Level 3,4,5)') }}
            @if(!$canEdit) <x-icon name="lock" class="w-3 h-3 opacity-50" /> @endif
        </legend>

        <div x-data="ckeditorHelper('penerimaan_komentar_ktt', {{ $canEdit ? 'false' : 'true' }})"
            wire:ignore
            wire:key="ck-ktt-{{ $canEdit ? 'edit' : 'view' }}">
            <div x-ref="editorElement" data-placeholder="{{ __('Masukkan Penerimaan & Komentar KTT...') }}"></div>
        </div>
        <x-label-error :messages="$errors->get('penerimaan_komentar_ktt')" />

        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
            <x-form.searchable-select2
                wire:key="select-penerimaan-komentar-ktt-id"
                placeholder="{{ __('Cari Nama KTT...') }}"
                modelsearch="searchNamePenerimaan.ktt"
                modelid="penerimaan_komentar_ktt_id"
                :options="$this->pelaporsPenerimaan"
                :showdropdown="$showPenerimaanKomentarKttDropdown && $canEdit"
                clickaction="selectPenerimaanKomentarKtt(VALUE_ID, VALUE_NAME)"
                :disabled="!$canEdit"
                x-on:focusin="$canEdit ? $wire.set('activeTypePenerimaan', 'penerimaan_komentar_ktt') : null" />
        </div>
    </fieldset>
    @endif
</div>