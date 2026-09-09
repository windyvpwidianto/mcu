<x-form.input-text label="Judul Insiden" model="title" required />
<div @class([ 'grid grid-cols-1 gap-2' , 'md:grid-cols-2'=> $this->isEnvironmentType,
    'md:grid-cols-1' => !$this->isEnvironmentType
    ])>
    <fieldset class="p-0 my-4 border shadow-md md:p-3 border-base-300 fieldset card bg-base-100">
        <legend class="text-sm font-semibold card-title ">{{ __('Klasifikasi Insiden') }}</legend>

        {{-- Hitung kolom secara dinamis: jika ada subtipe total 3 field, jika tidak total 2 field --}}
        <div @class([ 'grid grid-cols-1 gap-4' , 'md:grid-cols-3'=> $this->hasSubTypes,
            'md:grid-cols-2' => !$this->hasSubTypes,
            ])>
            {{-- Tipe Insiden --}}
            <x-form.select label="Tipe Insiden" model="event_type_id" :options="$eventTypes" option-label="event_type_name" required />

            {{-- Jenis Insiden (Conditional) --}}
            @if($this->hasSubTypes)
            <x-form.select label="Jenis Insiden" model="event_sub_type_id" :options="$eventSubTypes" option-label="event_sub_type_name" required />
            @endif

            {{-- Potensi LTI --}}
            <x-form.radio-group
                label="Potensi LTI/Fatality?"
                model="potential_lti"
                required />
        </div>
    </fieldset>

    {{-- Klasifikasi Lingkungan --}}
    @if($this->isEnvironmentType)
    <fieldset class="p-0 my-4 border shadow-md md:p-3 border-base-300 fieldset card bg-base-100" wire:transition>
        <legend class="text-sm font-semibold card-title ">{{ __('Klasifikasi Insiden Lingkungan') }}</legend>
        <div class="p-1">
            <x-form.select
                label="Tingkat Keparahan Lingkungan"
                model="env_classification"
                :options="$this->environmentalIncidentOptions"
                option-label="name"
                placeholder="Pilih Klasifikasi Lingkungan"
                required />
        </div>
    </fieldset>
    @endif
</div>
<div class="grid grid-cols-1 gap-2 mb-8 md:grid-cols-2 lg:grid-cols-3">
    <x-form.tgl-waktu label="Tanggal & Waktu Insiden" model="date_time" required />
    <x-form.search-template label="Lokasi" required modelsearch="searchLocation" modelid="location_id" :options="$locations" :showdropdown="$show_location" clickaction="selectLocation" namedb="name" />
    {{-- Lokasi spesifik muncul hanya jika lokasi utama sudah dipilih --}}
    @if ($location_id)
    <x-form.input-text label="Lokasi Spesifik" model="location_specific" placeholder="Masukkan detail lokasi spesifik..." required />
    <x-form.select
        label="Area Kontrak Karya"
        model="contract_area_name"
        :options="$this->contractAreaOptions"
        option-label="name"
        placeholder="Pilih Area Kontrak Karya"
        required />
    @endif
    <x-form.department-contractor-selector
        :dept-cont="$deptCont"
        :departments="$departments"
        :contractors="$contractors"
        model_dept="department_id"
        model_cont="contractor_id"
        :showDropdown="$showDropdown"
        :showContractorDropdown="$showContractorDropdown"
        :required="true" />

    <x-form.select
        label="PIC"
        model="penanggungJawab"
        :options="$penanggungJawabOptions"
        optionValue="id"
        optionLabel="name"
        placeholder="-- Pilih Penanggung Jawab --"
        required="true" />


    <x-form.searchable-select-advanced label="Dilaporkan Oleh" placeholder="Cari Nama Pelapor..."
        modelsearch="searchPelapor" modelid="pelapor_id" {{-- ID asli di DB --}} :options="$pelapors"
        :showdropdown="$showPelaporDropdown" {{-- Logic Manual --}} :manualMode="$manualPelaporMode"
        manualModelName="manualPelaporName" enableManualAction="enableManualPelapor"
        addManualAction="addPelaporManual" clickaction="selectPelapor" />
</div>
<fieldset class="p-0 my-4 border shadow-md md:p-3 border-base-300 fieldset card bg-base-100">
    <legend class="text-sm font-semibold card-title ">{{ __('Risk Parameters') }}</legend>
    <div class="flex flex-col-reverse gap-2 mt-2 md:flex-row">
        {{-- Kolom Likelihood & Consequence --}}
        <div class="space-y-4 md:grow">
            {{-- Consequence --}}
            <fieldset class="fieldset ">
                <x-form.label label="Consequence" required />
                <select wire:model.live="consequence_id"
                    class="select select-xs md:select-xs select-bordered w-full md:max-w-md focus-within:outline-none focus-within:border-info focus-within:ring-0 {{ $errors->has('consequence_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}">
                    <option value="">{{__('-- Pilih --')}}</option>
                    @foreach ($consequencess as $cons)
                    <option value="{{ $cons->id }}">{{ __($cons->name) }}</option>
                    @endforeach
                </select>
                <x-label-error :messages="$errors->get('consequence_id')" />

                @if ($consequence_id)
                @php
                $selectedConsequence = $consequencess->firstWhere('id', $consequence_id);
                @endphp
                @if ($selectedConsequence)
                <div
                    class="h-20 p-2 mt-1 overflow-y-auto text-sm text-gray-600 border rounded bg-gray-50">
                    {{ __($selectedConsequence->description) ?? 'Tidak ada deskripsi' }}
                </div>
                @endif
                @endif
            </fieldset>
            {{-- Likelihood --}}
            <fieldset class="fieldset ">
                <x-form.label label="Likelihood" required />
                <select wire:model.live="likelihood_id"
                    class="select select-xs md:select-xs select-bordered w-full md:max-w-md focus-within:outline-none focus-within:border-info focus-within:ring-0 {{ $errors->has('likelihood_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}">
                    <option value="">{{__('-- Pilih --')}}</option>
                    @foreach ($likelihoodss as $like)
                    <option value="{{ $like->id }}">{{ __($like->name) }}</option>
                    @endforeach
                </select>
                <x-label-error :messages="$errors->get('likelihood_id')" />

                @if ($likelihood_id)
                @php
                $selectedLikelihood = $likelihoodss->firstWhere('id', $likelihood_id);
                @endphp
                @if ($selectedLikelihood)
                <div
                    class="h-20 p-2 mt-1 overflow-y-auto text-sm text-gray-600 border rounded bg-gray-50">
                    {{ __($selectedLikelihood->description) ?? 'Tidak ada deskripsi' }}
                </div>
                @endif
                @endif
            </fieldset>
        </div>
        {{-- Kolom Risk Matrix --}}
        <div class="flex-none overflow-x-auto ">
            <table class="table table-xs w-60">
                <thead>
                    <tr class="text-center text-[9px]">
                        <td class=" border-1">{{ __('Level') }}</td>
                        <td class="text-white rotate_text border-1 bg-emerald-500">{{ __('Rendah') }}</td>
                        <td class="text-white bg-yellow-500 rotate_text border-1">{{ __('Sedang') }}</td>
                        <td class="text-white bg-orange-500 rotate_text border-1">{{ __('Tinggi') }}</td>
                        <td class="text-white rotate_text border-1 bg-rose-500">{{ __('Ekstrem') }}</td>
                        <td class="text-black bg-gray-100 rotate_text border-1">{{ __('Ditutup') }}</td>
                    </tr>
                    <tr class="text-center text-[9px]">
                        <th class="border-1">Likelihood ↓ / Consequence →</th>
                        @foreach ($consequences as $c)
                        <th class="rotate_text border-1">{{ __($c->name) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($likelihoods as $l)
                    <tr class="w-32 text-xs text-center">

                        <td class="w-1 font-bold border-1">{{ __($l->name) }}</td>
                        @foreach ($consequences as $c)
                        @php
                        $cell =
                        App\Models\RiskMatrixCell::where('likelihood_id', $l->id)
                        ->where('risk_consequence_id', $c->id)
                        ->first() ?? null;
                        $score = $l->level * $c->level;
                        $severity = $cell?->severity ?? '';
                        $color = match ($severity) {
                        'Rendah' => 'bg-emerald-500',
                        'Sedang' => 'bg-yellow-500',
                        'Tinggi' => 'bg-orange-500',
                        'Ekstrem' => 'bg-rose-500',
                        default => 'bg-gray-100',
                        };
                        @endphp
                        <td
                            class="border cursor-pointer   @if ($likelihood_id == $l->id && $consequence_id == $c->id) border-2 bg-primary border-primary-content @endif">
                            <span wire:click="edit({{ $l->id }}, {{ $c->id }})"
                                class="btn btn-square btn-xs   {{ $color }}">{{ Str::upper(substr(__($severity), 0, 1)) }}</span>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($RiskAssessment != null)
    <table class="table mt-4 table-xs table-zebra">

        <tr>
            <th class="w-40 text-xs border-base-300">Potential Risk Rating</th>
            <td class="pl-2 text-xs border-base-300">
                {{ $RiskAssessment->name }}
            </td>
        </tr>
        <tr>
            <th class="w-40 text-xs border-base-300">Notify</th>
            <td class="pl-2 text-xs border-base-300">
                {{ $RiskAssessment->reporting_obligation }}
            </td>
        </tr>
        <tr>
            <th class="w-40 text-xs border-base-300">Deadline</th>
            <td class="pl-2 text-xs border-base-300">{{ $RiskAssessment->notes }}</td>
        </tr>
        <tr>
            <th class="w-40 text-xs border-base-300">Coordinator</th>
            <td class="pl-2 text-xs border-base-300">
                {{ $RiskAssessment->coordinator }}
            </td>
        </tr>
    </table>
    @endif
</fieldset>
<x-form.text_area label="Tugas yang dilakukan" model="tasks" placeholder="{{ __('Contoh: Pembersihan tumpahan oli di area Workshop.') }}" required />
<x-form.text_area label="Narasi detail mengenai urutan kejadian (5W+1H)" :deskripsi="true" deskripsi_value="deskripsi_insident" model="description" placeholder="{{ __('Contoh: Siapa yang terlibat, Apa yang terjadi, Dimana, Kapan, Mengapa, dan Bagaimana urutannya.')}}" required />
<div class="p-4 border shadow-sm rounded-xl bg-base-100 border-base-300">
    <x-form.upload label="Visual Evidence" model="incident_photo" multiple keterangan="JPG, PNG (Max 2MB)" required />
    <div class="grid grid-cols-3 gap-2 mt-3">
        {{-- DATA TEMPORARY (NEW UPLOAD) --}}

        @foreach($saved_photos as $index => $path)
        <div class="avatar">
            <div class="relative w-40 rounded">
                {{-- Gunakan Storage::url karena file sudah tersimpan fisik --}}
                <img src="{{ \Storage::url($path) }}"
                    class="object-cover w-full h-full border-2 rounded-lg shadow-md border-primary" />

                <button type="button" wire:click="removeFile({{ $index }})"
                    class="absolute scale-75 -top-1 -right-1 btn btn-circle btn-primary btn-xs">
                    ✕
                </button>
            </div>
        </div>
        @endforeach

    </div>
</div>

<x-form.text_area label="Tindakan Darurat" :deskripsi="true" deskripsi_value="deskripsi_darurat" model="emergency_action" placeholder="{{ __('Jelaskan tindakan segera yang dilakukan setelah kejadian...')}}" required />
@if($this->isInjury)
<fieldset class="p-3 my-4 border shadow-md border-base-300 fieldset card bg-base-100">
    <legend class="text-sm font-semibold card-title text-primary">{{ __('Bagian Tubuh yang Terluka') }}</legend>

    <div class="grid grid-cols-1 gap-4">
        {{-- 1. Pilih Kategori --}}
        <x-form.select
            label="Kategori Bagian Tubuh"
            model="selectedBodyPartCategory"
            :options="$this->existingCategory"
            option-value="category"
            option-label="category"
            placeholder="-- {{__('Pilih Kategori untuk Memfilter Detail')}} --"
            required />

        {{-- 2. Detail Bagian Tubuh (Checkbox Grid) --}}
        @if ($selectedBodyPartCategory)
        <div class="p-4 border rounded-lg bg-base-200/30">
            <label class="block mb-3 text-xs font-bold uppercase text-base-content/60">
                {{ __('Pilih Detail') }} ({{ $selectedBodyPartCategory }})
            </label>
            <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                @foreach($this->detailsBodyPart as $part)
                <label class="flex items-center gap-3 p-3 transition-all border rounded-md cursor-pointer hover:bg-white bg-base-100 border-base-300 group" wire:key="body-part-{{ $part->id }}">
                    <input type="checkbox"
                        value="{{ $part->id }}" wire:model.live="selectedBodyParts" name="body_parts[]" {{-- Nama variabel harus sama --}}
                        class="checkbox checkbox-primary checkbox-sm">
                    <span class="text-sm group-hover:font-medium">{{ $part->name }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        {{-- 3. Ringkasan Terpilih (Badges) --}}
        @if(is_array($selectedBodyParts) && count($selectedBodyParts) > 0)
        <div class="flex flex-wrap gap-2 pt-3 border-t border-base-300">
            <span class="w-full text-xs font-medium text-base-500">{{ __('Terpilih:') }}</span>
            @foreach(\App\Models\BodyPart::whereIn('id', $selectedBodyParts)->get() as $selected)
            <div class="badge badge-primary badge-outline gap-2 p-3">
                <button wire:click="removeBodyPart({{ $selected->id }})" type="button" class="hover:text-error">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-4 h-4 stroke-current">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <span class="text-xs font-semibold">{{ $selected->name }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-xs italic text-error">
            * {{ __('Pilih setidaknya satu detail bagian tubuh.') }}
        </div>
        @endif
    </div>
</fieldset>
@else
<fieldset class="p-3 my-4 border shadow-md border-base-300 fieldset card bg-base-100">
    <legend class="text-sm font-semibold card-title ">{{ __('Kerusakan alat atau dampak lingkungan') }}</legend>
    <x-form.text_area label="Detail Kerusakan Alat / Lingkungan" :deskripsi="true" deskripsi_value="ket_insident" model="damage_detail" placeholder="{{ __('Jelaskan kerusakan alat atau dampak lingkungan...')}}" required />
</fieldset>
@endif