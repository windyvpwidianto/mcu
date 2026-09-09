<section class="w-full">
    <x-toast />
    {{-- Breadcrumb di sebelah kanan --}}
    <div class="flex justify-start mb-2 " wire:ignore>
        {{ Breadcrumbs::render('hazard-form') }}
    </div>
    @include('partials.manhours-heading')
    <x-manhours.layout>
        <form wire:submit.prevent="submit">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <fieldset class="fieldset">
                    <x-form.label label="Tipe Bahaya" required />
                    <select wire:model.live="tipe_bahaya"
                        class="select select-xs select-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 {{ $errors->has('tipe_bahaya') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}">
                        <option value="">{{__('-- Pilih --')}}</option>
                        @foreach ($eventTypes as $et)
                        <option value="{{ $et->id }}">{{ $et->event_type_name }}</option>
                        @endforeach
                    </select>
                    <x-label-error :messages="$errors->get('tipe_bahaya')" />
                </fieldset>
                <fieldset class="fieldset">
                    <x-form.label label="Jenis Bahaya" required />
                    <select wire:model.live="sub_tipe_bahaya"
                        class="select select-xs select-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 {{ $errors->has('sub_tipe_bahaya') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}"">
                        <option value="">{{__('-- Pilih --')}}</option>
                        @if ($tipe_bahaya)
                            @foreach ($subTypes as $et)
                                <option value=" {{ $et->id }}">{{ __($et->event_sub_type_name) }}</option>
                        @endforeach
                        @endif
                    </select>
                    <x-label-error :messages="$errors->get('sub_tipe_bahaya')" />
                </fieldset>
                <fieldset>
                    <input id="kta" value="kta" wire:model.live="keyWord"
                        class="peer/kta radio radio-xs radio-accent" type="radio" name="keyWord" checked />
                    <x-form.label for="kta" class="peer-checked/kta:text-accent text-[10px]"
                        label="Kondisi Tidak Aman" required />
                    <input id="tta" value="tta" wire:model.live="keyWord"
                        class="peer/tta radio radio-xs radio-primary" type="radio" name="keyWord" />
                    <x-form.label for="tta" class="peer-checked/tta:text-primary text-[10px]"
                        label="Tindakan Tidak Aman" required />
                    <div class="hidden peer-checked/kta:block ">
                        <select wire:model.live="kondisi_tidak_aman"
                            class="select select-xs mb-1 select-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 {{ $errors->has('kondisi_tidak_aman') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}">
                            <option value="">{{__('-- Pilih Kategori Bahaya --')}}</option>
                            @foreach ($ktas as $kta)
                            <option value="{{ $kta->id }}">{{ __($kta->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="hidden peer-checked/tta:block ">
                        <select wire:model.live="tindakan_tidak_aman"
                            class="select select-xs mb-1 select-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 {{ $errors->has('tindakan_tidak_aman') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}">
                            <option value="">{{__('-- Pilih Kategori Bahaya --')}}</option>
                            @foreach ($ttas as $tta)
                            <option value="{{ $tta->id }}">{{ __($tta->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($keyWord === 'kta')
                    <x-label-error :messages="$errors->get('kondisi_tidak_aman')" />
                    @endif
                    @if ($keyWord === 'tta')
                    <x-label-error :messages="$errors->get('tindakan_tidak_aman')" />
                    @endif
                </fieldset>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" wire:key="field-description">
                <fieldset class="mb-4 fieldset lg:col-span-2">
                    <x-form.label label="Deskripsi" required />
                    <div x-data="ckeditorHelper('description')" wire:ignore>
                        <div x-ref="editorElement" data-placeholder="{{ __('Masukkan deskripsi...') }}"></div>
                    </div>
                    <x-label-error :messages="$errors->get('description')" />
                </fieldset>

                <fieldset class=" fieldset">
                    <x-form.upload label="Lampirkan Foto Dokumentasi Deskripsi" model="doc_deskripsi"
                        :file="$doc_deskripsi" />
                    <div wire:loading.remove wire:target="doc_deskripsi">
                        @if ($doc_deskripsi)
                        @if (in_array($doc_deskripsi->getClientOriginalExtension(), ['jpg', 'jpeg', 'png']))
                        <img src="{{ $doc_deskripsi->temporaryUrl() }}"
                            class="mt-2 {{ $doc_deskripsi ? 'w-40' : '' }} h-auto rounded border" />
                        @elseif (in_array($doc_deskripsi->getClientOriginalExtension(), ['pdf', 'doc', 'docx']))
                        <div class="flex items-center gap-2 mt-2">
                            @if ($doc_deskripsi->getClientOriginalExtension() == 'pdf')
                            <x-icon.pdf class="w-8 h-8" />
                            <span
                                class="text-sm text-red-600">{{ $doc_deskripsi->getClientOriginalName() }}</span>
                            @elseif (in_array($doc_deskripsi->getClientOriginalExtension(), ['doc', 'docx']))
                            <x-icon.word class="w-8 h-8" />
                            <span
                                class="text-sm text-blue-600">{{ $doc_deskripsi->getClientOriginalName() }}</span>
                            @else
                            {{-- Ikon generik untuk file lain --}}
                            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zM6 20V4h7v4h4v12H6z" />
                            </svg>
                            <span class="text-sm text-gray-600">File:
                                {{ $doc_deskripsi->getClientOriginalName() }}</span>
                            @endif
                            @else
                            <p class="mt-2 text-sm text-gray-600">File:
                                {{ $doc_deskripsi->getClientOriginalName() }}
                            </p>
                            @endif
                            @endif
                        </div>
                        <x-label-error :messages="$errors->get('doc_deskripsi')" />
                </fieldset>
            </div>
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                <x-form.searchable-dropdown label="Lokasi" required modelsearch="searchLocation" modelid="location_id"
                    :options="$locations" :showdropdown="$show_location" clickaction="selectLocation" namedb="name" />
                {{-- Lokasi spesifik muncul hanya jika lokasi utama sudah dipilih --}}
                @if ($location_id)
                <fieldset class="fieldset">
                    <x-form.label label="Lokasi Spesifik" required />
                    <input name="location_specific" type="text" wire:model.live="location_specific"
                        placeholder="{{ __('Masukkan detail lokasi spesifik...') }}"
                        class=" input input-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs {{ $errors->has('location_specific') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                    <x-label-error :messages="$errors->get('location_specific')" />
                </fieldset>
                @endif
                <fieldset class="relative fieldset">
                    <x-form.label label="Tanggal & Waktu" required />
                    <div
                        class="{{ $errors->has('tanggal') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500 rounded' : 'ring-base-300 focus:ring-base-300 focus:border-base-300 rounded' }}">
                        <div class="relative " wire:ignore x-data="{
                            fp: null,
                            initFlatpickr() {
                                if (this.fp) this.fp.destroy();
                                this.fp = flatpickr(this.$refs.tanggalInput, {
                                    disableMobile: true,
                                    enableTime: true,
                                    time_24hr: true,
                                    defaultDate: this.$wire.entangle('tanggal').defer,
                                    dateFormat: 'd-m-Y H:i',
                                    clickOpens: true,
                                    // HAPUS ATAU KOMENTARI BARIS INI (appendTo)
                                    // appendTo: this.$refs.wrapper,

                                    // TAMBAHKAN ATAU UBAH OPSI POSITION
                                    position: 'auto-below', // Opsi ini akan memaksa kalender muncul di bawah input.

                                    onChange: (selectedDates, dateStr) => {
                                        this.$wire.set('tanggal', dateStr);
                                    }
                                });
                            }
                        }" x-ref="wrapper"
                            x-init="initFlatpickr();
                            Livewire.hook('message.processed', () => {
                                initFlatpickr();
                            });">
                            <input type="text" x-ref="tanggalInput" wire:model.live='tanggal'
                                placeholder="{{ __('Pilih Tanggal dan Waktu...') }}" readonly
                                class="input input-bordered cursor-pointer w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs {{ $errors->has('tanggal') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                        </div>
                    </div>
                    <x-label-error :messages="$errors->get('tanggal')" />
                </fieldset>
                <fieldset class="fieldset ">
                    <x-form.label label="Dilaporkan Oleh" required />
                    {{-- Induk harus memiliki class="relative" agar dropdown absolute berada di bawahnya --}}
                    <div class="relative">
                        <input name="searchPelapor" type="text" wire:model.live.debounce.300ms="searchPelapor"
                            placeholder="{{ __('Cari Nama Pelapor...') }}"
                            class="input input-bordered w-full max-w-sm focus-within:outline-none focus-within:border-info focus-within:ring-0 input-xs {{ $errors->has('pelapor_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}"
                            {{-- x-ref="searchInput" TIDAK LAGI DIBUTUHKAN --}} />
                        {{-- Menggunakan variabel Pelapor Anda: $showPelaporDropdown, $pelapors, selectPelapor --}}
                        @if ($showPelaporDropdown)
                        <ul
                            class="absolute z-10 w-full max-w-sm mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60">

                            {{-- Spinner ketika klik --}}
                            <div wire:loading wire:target="selectPelapor" class="p-2 text-center">
                                <span class="loading loading-spinner loading-sm text-secondary"></span>
                                {{-- {{ $manualPelaporMode }} --}}
                            </div>

                            @if (count($pelapors) > 0)
                            @foreach ($pelapors as $pelapor)
                            <li wire:click="selectPelapor({{ $pelapor->id }}, '{{ $pelapor->name }}')"
                                class="px-3 py-2 cursor-pointer hover:bg-base-200">
                                {{ $pelapor->name }}
                            </li>
                            @endforeach
                            @else
                            {{-- Logika untuk "Tambah pelapor manual" --}}
                            @if (!$manualPelaporMode)
                            <li class="px-3 py-2">
                                <flux:button size="xs" wire:click="enableManualPelapor"
                                    icon="plus" class="w-full cursor-pointer text-warning"
                                    variant="primary" color="cyan">
                                    Tidak ditemukan, tambah pelapor manual
                                </flux:button>
                            </li>
                            @else
                            <li class="px-3 py-2 text-sm text-gray-500">
                                Nama Pelapor Manual akan digunakan.
                            </li>
                            @endif
                            @endif
                        </ul>
                        @endif
                    </div>
                    @if ($manualPelaporMode)
                    <x-label-error :messages="$errors->get('manualPelaporName')" />
                    @else
                    <x-label-error :messages="$errors->get('pelapor_id')" />
                    @endif
                </fieldset>
            </div>
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                <fieldset class="mb-4 fieldset md:col-span-2" wire:key="field-immediate">

                    <x-form.label label="kondisi atau tindakan yang sudah dilakukan" required />
                    <div x-data="ckeditorHelper('immediate_corrective_action')" wire:ignore>
                        <div x-ref="editorElement" data-placeholder="{{ __('Masukkan kondisi atau tindakan yang sudah dilakukan...') }}"></div>
                    </div>

                    <x-label-error :messages="$errors->get('immediate_corrective_action')" />
                </fieldset>
                <fieldset class=" fieldset">
                    <x-form.upload label="Lampirkan foto atau dokumentasi" model="doc_corrective"
                        :file="$doc_corrective" />
                    <div wire:loading.remove wire:target="doc_corrective">
                        @if ($doc_corrective)
                        @if (in_array($doc_corrective->getClientOriginalExtension(), ['jpg', 'jpeg', 'png']))
                        <img src="{{ $doc_corrective->temporaryUrl() }}"
                            class="mt-2 {{ $doc_corrective ? 'w-40' : '' }} h-auto rounded border" />
                        @elseif (in_array($doc_corrective->getClientOriginalExtension(), ['pdf', 'doc', 'docx']))
                        <div class="flex items-center gap-2 mt-2">
                            @if ($doc_corrective->getClientOriginalExtension() == 'pdf')
                            <x-icon.pdf class="w-8 h-8" />
                            <span
                                class="text-sm text-red-600">{{ $doc_corrective->getClientOriginalName() }}</span>
                            @elseif (in_array($doc_corrective->getClientOriginalExtension(), ['doc', 'docx']))
                            <x-icon.word class="w-8 h-8" />
                            <span
                                class="text-sm text-blue-600">{{ $doc_corrective->getClientOriginalName() }}</span>
                            @else
                            {{-- Ikon generik untuk file lain --}}
                            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zM6 20V4h7v4h4v12H6z" />
                            </svg>
                            <span class="text-sm text-gray-600">File:
                                {{ $doc_corrective->getClientOriginalName() }}</span>
                            @endif
                        </div>
                        @else
                        <p class="mt-2 text-sm text-gray-600">File:
                            {{ $doc_corrective->getClientOriginalName() }}
                        </p>
                        @endif
                        @endif
                    </div>
                    <x-label-error :messages="$errors->get('doc_corrective')" />
                </fieldset>
            </div>
            <fieldset class="p-3 my-4 border shadow-md border-base-300 fieldset card bg-base-100">
                <legend class="text-sm font-semibold card-title ">{{ __('Penanggung Jawab') }}</legend>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:mb-4 ">
                    <fieldset>
                        <input id="department" value="department" wire:model="deptCont"
                            class="peer/department radio radio-xs radio-accent" type="radio" name="deptCont"
                            checked />
                        <x-form.label for="department" class="peer-checked/department:text-accent text-[10px]"
                            label="PT. MSM & PT. TTN" required />
                        <input id="company" value="company" wire:model="deptCont"
                            class="peer/company radio radio-xs radio-primary" type="radio" name="deptCont" />
                        <x-form.label for="company" class="peer-checked/company:text-primary" label="Kontraktor"
                            required />

                        <div class="hidden mt-2 peer-checked/department:block">
                            {{-- Department --}}
                            <div class="relative mb-1">
                                <x-form.searchable-dropdown-without-label modelsearch="search" modelid="department_id"
                                    placeholder="Cari Departemen..." :options="$departments" :showdropdown="$showDropdown"
                                    clickaction="selectDepartment" namedb="department_name" />
                            </div>
                        </div>
                        <div class="hidden mt-2 peer-checked/company:block">
                            {{-- Contractor --}}
                            <div class="relative mb-1">
                                <x-form.searchable-dropdown-without-label modelsearch="searchContractor"
                                    placeholder="Cari Kontraktor..." modelid="contractor_id" :options="$contractors"
                                    :showdropdown="$showContractorDropdown" clickaction="selectContractor" namedb="contractor_name" />
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="fieldset">
                        <x-form.label label="PIC" required />
                        <select wire:model.live="penanggungJawab"
                            class="select select-xs select-bordered w-full focus-within:outline-none focus-within:border-info focus-within:ring-0 {{ $errors->has('penanggungJawab') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}">
                            <option value="">{{__('-- Pilih --')}}</option>
                            @foreach ($penanggungJawabOptions as $pj)
                            <option value="{{ $pj['id'] }}">{{ $pj['name'] }}</option>
                            @endforeach
                        </select>
                        <x-label-error :messages="$errors->get('penanggungJawab')" />
                    </fieldset>
                </div>
            </fieldset>

            <fieldset class="p-3 my-4 border shadow-md border-base-300 fieldset card bg-base-100">
                <legend class="text-sm font-semibold card-title "> {{ __('Status Area Saat Ini?') }}</legend>
                <div class="flex gap-4">
                    <div class="flex items-center gap-2">
                        <label class="label">
                            <input type="radio" name="action_trigger" value="open"
                                wire:model.live="showActionModal"
                                class="radio radio-xs bg-error/40 border-error/20 checked:bg-error/50 checked:text-error-content checked:border-error" />
                            {{ __('Aman Sementara') }}
                        </label>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="label">
                            <input type="radio" name="action_trigger" value="close"
                                wire:model.live="showActionModal"
                                class="radio radio-xs bg-success/40 border-success/20 checked:bg-success/10 checked:text-success-content checked:border-success " />
                            {{ __('Sudah Aman Total') }}
                        </label>
                    </div>
                </div>
                <div class="my-4 text-xs divider">{{ __('Daftar Tindakan Terinput') }}</div>
                <div class="overflow-y-auto max-h-60">
                    <ul class="space-y-2">
                        @forelse ($actions as $index => $action)
                        <li class="flex items-start justify-between p-2 border rounded-md bg-base-200 gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium">{!! $action['description'] !!}</div>
                                <p class="mt-1 text-[10px] opacity-70">
                                    Due: {{ $action['due_date'] ?: '-' }} | PIC ID: {{ $action['responsible_id'] ?: '-' }}
                                </p>

                                {{-- === TAMPILKAN PREVIEW DOKUMEN FINAL SEMENTARA === --}}
                                @if(!empty($action['final_doc']))
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach($action['final_doc'] as $file)
                                    <a href="{{ asset('storage/' . $file) }}" target="_blank"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-medium text-success-content bg-success/20 border border-success/30 rounded hover:bg-success/30 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paperclip">
                                            <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                                        </svg>
                                        <span class="truncate max-w-[150px]">{{ basename($file) }}</span>
                                    </a>
                                    @endforeach
                                </div>
                                @endif
                            </div>

                            <flux:button variant="danger" size="xs" wire:click="removeAction({{ $index }})" icon="trash" class="shrink-0 mt-0.5" />
                        </li>
                        @empty
                        <li class="py-4 text-sm italic text-center text-gray-400 border-2 border-dashed rounded-lg">
                            {{ __('Belum ada tindakan.') }}
                        </li>
                        @endforelse
                    </ul>
                </div>
            </fieldset>

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
                    <div role="tablist" class="flex">


                    </div>
                    <table class="table table-xs w-60">
                        <thead>
                            <tr class="text-center text-[9px]">
                                <td class=" border-1">{{ __('Level') }}</td>
                                <td class="rotate_text border-1 bg-emerald-500">{{ __('Rendah') }}</td>
                                <td class="bg-yellow-500 rotate_text border-1">{{ __('Sedang') }}</td>
                                <td class="bg-orange-500 rotate_text border-1">{{ __('Tinggi') }}</td>
                                <td class="rotate_text border-1 bg-rose-500">{{ __('Ekstrem') }}</td>
                                <td class="bg-gray-100 rotate_text border-1">{{ __('Ditutup') }}</td>
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
                    <th class="w-40 text-xs border-slate-400">Potential Risk Rating</th>
                    <td class="pl-2 text-xs border-slate-400">
                        {{ $RiskAssessment->name }}
                    </td>
                </tr>
                <tr>
                    <th class="w-40 text-xs border-slate-400">Notify</th>
                    <td class="pl-2 text-xs border-slate-400">
                        {{ $RiskAssessment->reporting_obligation }}
                    </td>
                </tr>
                <tr>
                    <th class="w-40 text-xs border-slate-400">Deadline</th>
                    <td class="pl-2 text-xs border-slate-400">{{ $RiskAssessment->notes }}</td>
                </tr>
                <tr>
                    <th class="w-40 text-xs border-slate-400">Coordinator</th>
                    <td class="pl-2 text-xs border-slate-400">
                        {{ $RiskAssessment->coordinator }}
                    </td>
                </tr>
            </table>
            @endif
            <div class="flex justify-end hidden mt-4 md:block">
                <flux:button wire:loading.attr="disabled" wire:target="submit,doc_corrective,doc_deskripsi" size="xs" type="submit" icon:trailing="send" variant="primary">{{ __('Kirim Laporan') }}
                </flux:button>
            </div>
            <div class="block mt-4 md:hidden">
                <flux:button wire:loading.attr="disabled" wire:target="submit,doc_corrective,doc_deskripsi" size="xs" class="w-full" type="submit" icon:trailing="send" variant="primary">
                    {{ __('Kirim Laporan') }}
                </flux:button>
            </div>
        </form>
        @include('livewire.hazard.modal-action')

    </x-manhours.layout>

</section>