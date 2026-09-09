<section class="w-full">
    <x-toast />

    <div class="flex justify-start " wire:ignore>
        @php
            $currentRoute = Route::currentRouteName();
        @endphp

        @if (Breadcrumbs::exists($currentRoute))
            {!! Breadcrumbs::render($currentRoute, isset($inspectionId) ? $inspectionId : null) !!}
        @endif
    </div>

    <x-tabs-wpi.layout>
        <div class="p-6 bg-white rounded-lg shadow">

            {{-- SELEKSI JENIS ALAT --}}
            <div class="mb-4">
                <fieldset class="w-full fieldset md:max-w-80">
                    <x-form.label label="Pilih Jenis Alat" required />
                    <select wire:model.live="type"
                        class="select select-xs select-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden {{ $errors->has('type') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}">
                        <option value="">-- Pilih --</option>
                       @foreach ($availableTypes as $typeName)
                            <option value="{{ $typeName }}">{{ $typeName }}</option>
                        @endforeach
                    </select>
                    <x-label-error :messages="$errors->get('type')" />
                </fieldset>
            </div>

            {{-- HEADER INFO (AREA, LOKASI, TANGGAL) --}}
            <div class="grid grid-cols-1 gap-2 mb-4 md:grid-cols-3">
                {{-- Menggunakan logic dari Trait: searchLocation --}}
                <x-form.search-floating label="Area" required modelsearch="searchLocation" modelid="location_id"
                    placeholder="Cari Area..." :options="$locations" :showdropdown="$show_location" clickaction="selectLocation"
                    namedb="name" />

                <x-form.input-floating label="Lokasi Spesifik" model="location" required />
                <x-form.datepicker label="Tanggal / Date" model="inspection_date" />
            </div>

            {{-- SECTION DYNAMIS INPUTS & CHECKBOXES --}}
            @if ($type && isset($fields[$type]))
                <div class="p-4 mb-4 border border-gray-200 rounded-lg bg-gray-50">
                    {{-- Inputs Teks Dinamis (FE No, Box No, dll) --}}
                    <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-3">
                        @if (isset($fields[$type]['inputs']))
                            @foreach ($fields[$type]['inputs'] as $inputField)
                                <fieldset class="fieldset">
                                    <label class="floating-label">
                                        <input type="text" wire:key="tech-{{ $type }}-{{ $inputField }}"
                                            wire:model.live="technical_data.{{ $inputField }}" readonly
                                            class="w-full text-gray-700 bg-gray-100 cursor-not-allowed input-xs input input-bordered" />

                                        <span>
                                            {{ $inputField }}
                                            <span class="text-xs italic text-gray-400">(Auto)</span>
                                        </span>
                                    </label>
                                </fieldset>
                            @endforeach
                        @endif
                    </div>

                    <h3 class="mb-3 text-sm font-bold text-gray-700">Kondisi Checklist ({{ $type }}):</h3>

                    {{-- Checkbox Dinamis --}}
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-4">
                        @if (isset($fields[$type]['checks']))
                            @foreach ($fields[$type]['checks'] as $field)
                                <fieldset class="p-2 bg-white border border-gray-200 rounded-md">
                                    <label class="flex items-center justify-between text-xs cursor-pointer">
                                        <span
                                            class="font-semibold tracking-wider text-gray-600 uppercase">{{ $field }}</span>
                                        <input type="checkbox" wire:key="check-{{ $type }}-{{ $field }}"
                                            wire:model="conditions.{{ $field }}"
                                            class="checkbox checkbox-xs border-rose-600 bg-rose-500 checked:border-emerald-500 checked:bg-emerald-400 checked:text-emerald-800" />
                                    </label>
                                    <x-label-error :messages="$errors->get('conditions.' . $field)" />
                                </fieldset>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif

            <x-form.textarea label="Remarks/Catatan" required model="remarks" placeholder="Remarks/Catatan..." />

            <div class="flex flex-col items-start justify-between gap-4 pt-2 mt-2 border-t md:flex-row">
                {{-- UPLOAD DOKUMENTASI --}}
                <div class="w-full md:max-w-md">
                    <x-form.upload label="Foto Area (Dokumentasi Lokasi)" model="foto_area" :file="$foto_area" />
                    <div wire:loading.remove.class='hidden' wire:target="foto_area"
                        class="hidden mt-1 text-xs text-primary">Mengunggah gambar...</div>

                    @error('foto_area')
                        <div class="invalid-feedback text-danger">{{ $message }}</div>
                    @enderror

                    @if ($foto_area)
                        <div class="mt-2 position-relative d-inline-block">
                            <img src="{{ $foto_area->temporaryUrl() }}" style="height: 150px;"
                                class="img-thumbnail border-primary">
                            <span class="top-0 badge bg-primary position-absolute start-0">Baru</span>
                            <label wire:click="clearNewFotoArea"
                                class="top-0 btn btn-error btn-xs btn-square position-absolute end-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-trash2-icon lucide-trash-2">
                                    <path d="M10 11v6" />
                                    <path d="M14 11v6" />
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                </svg>
                            </label>
                        </div>
                    @elseif ($area_photo_path)
                        <div class="mt-2 position-relative d-inline-block">
                            <img src="{{ asset('storage/' . $area_photo_path) }}" style="height: 150px;"
                                class="img-thumbnail border-secondary">
                            <span class="top-0 badge bg-secondary position-absolute start-0">Lama</span>
                            <label
                                onclick="confirm('Hapus foto area ini secara permanen?') || event.stopImmediatePropagation()"
                                wire:click="removeAreaPhoto"
                                class="top-0 btn btn-error btn-xs btn-square position-absolute end-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-trash2-icon lucide-trash-2">
                                    <path d="M10 11v6" />
                                    <path d="M14 11v6" />
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                </svg>
                            </label>
                        </div>
                    @endif
                </div>
                <div class="w-full md:max-w-md">
                    <fieldset class="fieldset">
                        <x-form.upload label="Foto Temuan" model="dokumentasi" :file="$dokumentasi" />

                        <div wire:loading.remove wire:target="dokumentasi">
                            {{-- CASE 1: Tampilkan File Baru yang Sedang Di-upload --}}
                            @if ($dokumentasi)
                                <div class="relative inline-block mt-2 group">
                                    {{-- Tombol Hapus File Baru --}}
                                    <button type="button" wire:click="clearNewUpload"
                                        class="absolute z-10 p-1 text-white bg-red-600 rounded-full shadow-lg -top-2 -right-2 hover:bg-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>

                                    @php $ext = $dokumentasi->getClientOriginalExtension(); @endphp
                                    @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                                        <img src="{{ $dokumentasi->temporaryUrl() }}"
                                            class="w-40 h-auto border rounded shadow-sm" />
                                    @else
                                        <div class="flex items-center gap-2 p-2 border rounded bg-gray-50 text-info">
                                            @if ($ext == 'pdf')
                                                <x-icon.pdf class="w-8 h-8" />
                                            @else
                                                <x-icon.word class="w-8 h-8" />
                                            @endif
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold">File Baru:</span>
                                                <span
                                                    class="text-xs truncate max-w-[100px]">{{ $dokumentasi->getClientOriginalName() }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- CASE 2: Tampilkan File Lama dari Database --}}
                            @elseif (isset($old_documentation) && $old_documentation)
                                <div class="relative inline-block mt-2 group">
                                    {{-- Tombol Hapus File di Database --}}
                                    <button type="button"
                                        onclick="confirm('Hapus file ini secara permanen?') || event.stopImmediatePropagation()"
                                        wire:click="deleteOldFile"
                                        class="absolute z-10 p-1 text-white rounded-full shadow-lg bg-rose-600 -top-2 -right-2 hover:bg-rose-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>

                                    @php
                                        $ext = pathinfo($old_documentation, PATHINFO_EXTENSION);
                                        $fileName = basename($old_documentation);
                                    @endphp

                                    @if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                                        <div class="w-40">
                                            <span
                                                class="absolute top-0 left-0 bg-black/50 text-white text-[10px] px-1 rounded-br">File
                                                Saat Ini</span>
                                            <img src="{{ asset('storage/' . $old_documentation) }}"
                                                class="w-40 h-auto border rounded shadow-sm" />
                                        </div>
                                    @else
                                        <div
                                            class="flex items-center gap-2 p-2 border border-blue-200 rounded bg-blue-50">
                                            @if (strtolower($ext) == 'pdf')
                                                <x-icon.pdf class="w-8 h-8 text-red-500" />
                                            @else
                                                <x-icon.word class="w-8 h-8 text-blue-500" />
                                            @endif
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-bold text-blue-600 uppercase">Dokumen
                                                    Tersimpan:</span>
                                                <a href="{{ asset('storage/' . $old_documentation) }}" target="_blank"
                                                    class="text-xs text-blue-700 underline truncate max-w-[100px] hover:text-blue-900">
                                                    {{ $fileName }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <x-label-error :messages="$errors->get('dokumentasi')" />
                    </fieldset>
                </div>


                {{-- MULTIPLE PELAPOR (Gunakan Logic Trait) --}}
                <div class="w-full md:max-w-md">
                    <x-form.searchable-select-advanced label="Dilaporkan Oleh" placeholder="Cari Nama Pelapor..."
                        modelsearch="searchResponsibility" modelid="location_id" {{-- ID dummy untuk select, data asli masuk ke array $inspected_users --}}
                        :options="$pelapors" :showdropdown="$showPelaporDropdown" :manualMode="$manualPelaporMode" manualModelName="manualPelaporName"
                        enableManualAction="enableManualPelapor" addManualAction="enableManualPelapor"
                        {{-- Memanggil fungsi yang sama untuk push manual --}} clickaction="selectPelapor" />

                    {{-- Badge Daftar Pelapor yang Terpilih --}}
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach ($inspected_users as $index => $user)
                            <div
                                class="flex items-center gap-1 px-2 py-1 text-xs font-medium border rounded shadow-xs bg-info/10 text-info border-info/20">
                                <span>{{ $user['name'] }}</span>
                                <button type="button" wire:click="removeInspectedUser({{ $index }})"
                                    class="transition-colors hover:text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <x-label-error :messages="$errors->get('inspected_users')" />
                </div>
            </div>

            <div class="mt-4 modal-action">
                <button wire:click="update" wire:loading.attr="disabled" class="btn btn-soft btn-success btn-sm">
                    <span wire:loading.remove.class='hidden' class="hidden" wire:target="update"
                        class="loading loading-spinner loading-xs"></span>
                    Simpan Laporan
                </button>
            </div>
        </div>
    </x-tabs-wpi.layout>
</section>
