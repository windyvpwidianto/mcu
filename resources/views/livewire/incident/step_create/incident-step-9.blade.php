<div class="w-full">
    <fieldset class="w-full p-4 mt-4 border fieldset bg-base-200 border-base-300 rounded-box">
        <legend class="fieldset-legend">Penerimaan & Komentar Project Manager: (Jika kontraktor)</legend>
        <div x-data="ckeditorHelper('penerimaan_komentar_contractor')" wire:ignore>
            <div x-ref="editorElement" data-placeholder="Masukkan Penerimaan & Komentar Project Manager: (Jika kontraktor) ..."></div>
        </div>
        <x-label-error :messages="$errors->get('penerimaan_komentar_contractor')" />

        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
            <x-form.searchable-select2 wire:key="select-penerimaan-komentar-contractor"
                placeholder="Cari Nama..." modelsearch="searchNamePenerimaan.kontraktor"
                modelid="penerimaan_komentar_contractor_id"
                :options="$this->pelaporsPenerimaan"
                :showdropdown="$showPenerimaanKomentarContractorDropdown"
                clickaction="selectPenerimaanKomentarContractor(VALUE_ID, VALUE_NAME)"
                x-on:focusin="$wire.set('activeTypePenerimaan', 'penerimaan_komentar_contractor'); $wire.set('activeIndexPenerimaan', 0)" />
        </div>


    </fieldset>
    <fieldset class="w-full p-4 mt-2 border fieldset bg-base-200 border-base-300 rounded-box">
        <legend class="fieldset-legend">Penerimaan & Komentar Project Manager</legend>
        <div x-data="ckeditorHelper('penerimaan_komentar_internal')" wire:ignore>
            <div x-ref="editorElement" data-placeholder="Masukkan Penerimaan & Komentar Project Manager..."></div>
        </div>
        <x-label-error :messages="$errors->get('penerimaan_komentar_internal')" />

        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
            <x-form.searchable-select2 wire:key="select-penerimaan-komentar-internal"
                placeholder="Cari Nama..." modelsearch="searchNamePenerimaan.internal"
                modelid="penerimaan_komentar_internal_id"
                :options="$this->pelaporsPenerimaan"
                :showdropdown="$showPenerimaanKomentarInternalDropdown"
                clickaction="selectPenerimaanKomentarInternal(VALUE_ID, VALUE_NAME)"
                x-on:focusin="$wire.set('activeTypePenerimaan', 'penerimaan_komentar_internal'); $wire.set('activeIndexPenerimaan', 0)" />
        </div>


    </fieldset>

    <fieldset class="w-full p-4 mt-2 border fieldset bg-base-200 border-base-300 rounded-box">
        <legend class="fieldset-legend">Penerimaan & Komentar OHS Dept Head</legend>
        <div x-data="ckeditorHelper('penerimaan_komentar_ohs')" wire:ignore wire:key="select-penerimaan-ohs">
            <div x-ref="editorElement" data-placeholder="Masukkan Penerimaan & Komentar OHS Dept Head..."></div>
        </div>
        <x-label-error :messages="$errors->get('penerimaan_komentar_ohs')" />

        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
            <x-form.searchable-select2 wire:key="select-penerimaan-komentar-ohs"
                placeholder="Cari Nama..." modelsearch="searchNamePenerimaan.ohs"
                modelid="penerimaan_komentar_ohs_id"
                :options="$this->pelaporsPenerimaan"
                :showdropdown="$showPenerimaanKomentarOhsDropdown"
                clickaction="selectPenerimaanKomentarOhs(VALUE_ID, VALUE_NAME)"
                x-on:focusin="$wire.set('activeTypePenerimaan', 'penerimaan_komentar_ohs'); $wire.set('activeIndexPenerimaan', 0)" />
        </div>


    </fieldset>
    @if(in_array((int)$consequence_id, [3, 4, 5]))
    {{-- Menggunakan key yang unik berdasarkan level agar re-render sempurna --}}
    <fieldset wire:key="fieldset-ktt-{{ $consequence_id }}"
        class="w-full p-4 mt-2 border fieldset bg-base-200 border-base-300 rounded-box">

        <legend class="fieldset-legend">
            Penerimaan & Komentar KTT: (Hanya untuk insiden dengan aktual Level 3,4,5)
        </legend>

        {{-- CKEditor dengan wire:ignore agar tidak tertimpa re-render Livewire --}}
        <div x-data="ckeditorHelper('penerimaan_komentar_ktt')"
            wire:ignore
            wire:key="ckeditor-ktt-wrapper">
            <div x-ref="editorElement"
                data-placeholder="Masukkan Penerimaan & Komentar KTT: (Hanya untuk insiden dengan aktual Level 3,4,5)...">
            </div>
        </div>

        {{-- Pesan Error di bawah Editor --}}
        <x-label-error :messages="$errors->get('penerimaan_komentar_ktt')" />

        <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
            {{-- Select2 Nama KTT --}}
            <x-form.searchable-select2
                wire:key="select-penerimaan-komentar-ktt-id"
                placeholder="Cari Nama..."
                modelsearch="searchNamePenerimaan.ktt"
                modelid="penerimaan_komentar_ktt_id"
                :options="$this->pelaporsPenerimaan"
                :showdropdown="$showPenerimaanKomentarKttDropdown"
                clickaction="selectPenerimaanKomentarKtt(VALUE_ID, VALUE_NAME)"
                x-on:focusin="$wire.set('activeTypePenerimaan', 'penerimaan_komentar_ktt'); $wire.set('activeIndexPenerimaan', 0)" />
        </div>

    </fieldset>
    @endif
</div>