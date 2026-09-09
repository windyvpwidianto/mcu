<section class="w-full">
    <x-toast />

    <div class="flex justify-start " wire:ignore>
        @php $currentRoute = Route::currentRouteName(); @endphp
        @if (Breadcrumbs::exists($currentRoute))
        {!! Breadcrumbs::render($currentRoute, isset($reportId) ? $reportId : null) !!}
        @endif
    </div>
    <x-tabs-wpi.layout>
        <div class="mb-2 bg-white ">
            <div class="grid content-center w-full grid-cols-1 gap-2 md:grid-cols-4">
                <div class="mt-0.5">
                    <select wire:model.live="type"
                        class="select select-xs select-bordered w-full max-w-sm focus-within:outline-none focus-within:border-info focus-within:ring-0 {{ $errors->has('type') ? 'border-rose-500' : '' }}">
                        <option value="">-- Pilih Jenis Alat --</option>
                        @foreach ($availableTypes as $typeName)
                        <option value="{{ $typeName }}">{{ $typeName }}</option>
                        @endforeach
                    </select>
                </div>
                <x-label-error :messages="$errors->get('type')" />

                <x-form.search-floating label="Area" required modelsearch="searchLocation" modelid="location_id"
                    placeholder="Area..." :options="$locations" :showdropdown="$show_location" clickaction="selectLocation"
                    namedb="name" />
                @if ($location_id)
                <div class="mt-0.5">
                    <select wire:model.live="selected_location"
                        class="w-full select select-bordered select-xs focus-within:outline-none focus-within:border-info focus-within:ring-0">
                        <option value="">-- Pilih Lokasi Spesifik --</option>
                        @foreach ($equipmentMasters as $t)
                        <option value="{{ $t->specific_location }}">{{ $t->specific_location }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <x-form.datepicker label="Tanggal / Date" model="inspection_date" />
            </div>
        </div>
        <div class="relative overflow-hidden border rounded-lg shadow-inner bg-slate-50">
            @php
            // Ambil checks dari database (config fields)
            $checks = $fields[$type]['checks'] ?? [];
            $inputs = $fields[$type]['inputs'] ?? [];

            // Ambil technical keys dari kolom JSON 'technical_data' milik alat pertama yang muncul
            $firstEquipment = $allMasterData->first();
            $techKeys =
            $firstEquipment && is_array($firstEquipment->technical_data)
            ? array_keys($firstEquipment->technical_data)
            : [];
            @endphp

            <div class="overflow-x-auto max-h-[calc(100vh-25rem)] 2xl:max-h-[calc(100vh-37rem)]  border rounded-lg ">
                @if (!empty($firstEquipment->location_id))
                <table class="table border-separate md:table-fixed table-xs table-pin-rows border-spacing-0">
                    <thead>
                        <tr class="capitalize bg-slate-100 text-slate-700">
                            <th class="border-b border-r bg-slate-100 text-[10px]">Location</th>
                            @foreach ($inputs as $techKey)
                            <th class="...">
                                {{-- Cek jika $techKey adalah array/objek, ambil properti 'label' nya --}}
                                @if(is_array($techKey))
                                {{ $techKey['label'] ?? 'N/A' }}
                                @else
                                {{ $techKey }}
                                @endif
                            </th>
                            @endforeach
                            @foreach ($checks as $checkItem)
                            <th class="text-center border-b border-r bg-amber-50 text-amber-700 text-[10px] capitalize px-1">
                                {{-- Jika array ambil 'name', jika data lama (string) tampilkan langsung --}}
                                {{ is_array($checkItem) ? $checkItem['name'] : $checkItem }}
                            </th>
                            @endforeach

                            <th class=" text-center capitalize border-b border-r text-[10px]">Remarks</th>
                            <th class=" text-center capitalize border-b text-[10px] w-12 md:w-12 2xl:w-36">Foto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($allMasterData as $master)
                        <tr class="transition-colors hover:bg-blue-50/30">
                            <td class="text-xs font-medium bg-white border-b border-r ">
                                {{ $master->specific_location }}
                            </td>

                            @foreach ($techKeys as $key)
                            <td class="italic text-center border-b border-r bg-slate-50/50 text-slate-500">
                                {{-- Ambil langsung dari kolom JSON master data --}}
                                {{ $master->technical_data[$key] ?? '-' }}
                            </td>
                            @endforeach

                            {{-- --- PERBAIKAN DISINI --- --}}
                            @php
                            /** * Mencari konfigurasi checklist yang sesuai dengan specific_location alat ini.
                            * Kita ambil dari array masterCheckColumns yang sudah kita siapkan di Controller (FireInspection.php)
                            * Jika tidak ada, fallback ke $checks global.
                            */
                            $currentMasterChecks = $masterCheckColumns[$master->id] ?? $checks;
                            @endphp

                            @foreach ($currentMasterChecks as $checkItem)
                            @php
                            $name = is_array($checkItem) ? $checkItem['name'] : $checkItem;
                            $inputType = is_array($checkItem) ? ($checkItem['type'] ?? 'checkbox') : 'checkbox';
                            @endphp

                            <td class="px-1 text-center bg-white border-b border-r">
                                @if($inputType === 'text')
                                <input type="text"
                                    wire:key="text-{{ $master->id }}-{{ $name }}"
                                    wire:model.blur="conditions.{{ $master->id }}.{{ $name }}"
                                    class="input input-bordered input-xs w-full max-w-[70px] text-[10px] focus:outline-none"
                                    placeholder="..." />
                                @else
                                <input type="checkbox"
                                    wire:key="check-{{ $master->id }}-{{ $name }}"
                                    wire:model.live="conditions.{{ $master->id }}.{{ $name }}"
                                    class="checkbox checkbox-xs border-emerald-600 checked:bg-emerald-500" />
                                @endif
                            </td>
                            @endforeach
                            {{-- --- END PERBAIKAN --- --}}

                            <td class="p-1 bg-white border-b border-r ">
                                <x-form.textarea row='1' model="conditions.{{ $master->id }}.remarks"
                                    placeholder="Remarks" />
                            </td>
                            <td class="p-2 text-center bg-white border-b" wire:loading.add.class="skeleton" wire:target="dokumentasi.{{ $master->id }}">
                                <div class="flex flex-col items-center justify-center">
                                    <input type="file" id="file-{{ $master->id }}" class="hidden"
                                        wire:model="dokumentasi.{{ $master->id }}">

                                    @if (isset($dokumentasi[$master->id]))

                                    <div class="relative inline-block group">
                                        <img src="{{ $dokumentasi[$master->id]->temporaryUrl() }}"
                                            class="object-cover w-20 h-20 border rounded-md shadow-sm">

                                        <label for="file-{{ $master->id }}"
                                            class="absolute inset-0 flex items-center justify-center transition-opacity rounded-md opacity-0 cursor-pointer bg-black/40 group-hover:opacity-100">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="w-4 h-4 text-white" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2">
                                                <path
                                                    d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                            </svg>
                                        </label>

                                        <button type="button"
                                            wire:click="removeImage({{ $master->id }})"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-0.5 shadow-lg hover:bg-red-600 transition-colors cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="3">
                                                <path d="M18 6 6 18M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    @else
                                    <div class="tooltip md:tooltip-left">
                                        <div class="z-40 tooltip-content">
                                            <div class="text-sm font-black text-orange-400 animate-bounce">
                                                upload foto</div>
                                        </div>
                                        <label for="file-{{ $master->id }}" wire:loading.add.class="skeleton" wire:target="dokumentasi.{{ $master->id }}"
                                            class=" btn btn-ghost btn-xs text-info hover:bg-info/10">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <rect width="18" height="18" x="3" y="3"
                                                    rx="2" ry="2" />
                                                <circle cx="9" cy="9" r="2" />
                                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                            </svg>
                                        </label>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="100" class="py-10 text-center bg-slate-50 text-slate-400">
                                <p class="italic">Tidak ada data alat ditemukan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @else
                <div
                    class="flex items-center justify-center h-40 border border-dashed rounded-lg bg-slate-100 border-slate-300">
                    <p class="text-sm italic text-slate-400">Pilih Area untuk menampilkan data inspeksi.</p>
                </div>
                @endif

            </div>
        </div>

        <div class="flex flex-col items-start justify-between gap-4 pt-2 mt-2 border-t md:flex-row">
            <div class="w-full md:max-w-md">
                <x-form.upload label="Foto Inspeksi" model="foto_area" :file="$foto_area" />
                <div wire:loading.remove.class='hidden' wire:target="foto_area"
                    class="hidden mt-1 text-xs text-primary">Mengunggah gambar...</div>
                @error('foto_area')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                @if ($foto_area)
                <div class="mt-2 position-relative d-inline-block">
                    <img src="{{ $foto_area->temporaryUrl() }}" style="height: 150px;" class="img-thumbnail">
                    <button type="button" wire:click="removeFotoArea"
                        class="top-0 btn btn-danger btn-sm position-absolute end-0">
                        &times;
                    </button>
                </div>
                @endif
            </div>
            <div class="w-full md:max-w-md">
                <x-form.searchable-select-advanced label="Pemeriksa" placeholder="Cari Nama Pemeriksa..."
                    modelsearch="searchResponsibility" modelid="inspected_users" :options="$pelapors"
                    :showdropdown="$showPelaporDropdown" :manualMode="$manualPelaporMode" manualModelName="manualPelaporName"
                    enableManualAction="enableManualPelapor" addManualAction="addPelaporManual"
                    clickaction="selectPelapor" />

                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach ($inspected_users as $index => $user)
                    <div class="gap-2 badge-soft badge-info badge   text-[10px] badge-xs">
                        {{ $user['name'] }}
                        <button wire:click="removeInspectedUser({{ $index }})" class="hover:text-error">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
        <div class="mt-4 modal-action">
            <flux:button wire:click="save" size="xs" wire:loading.attr="disabled" variant="primary" wire:target="save,foto_area">

                <span wire:loading.add.class='hidden' wire:target="save,foto_area">🚀 Simpan Laporan Inspeksi</span>

                <span wire:loading.remove.class="hidden" class="hidden" wire:target="save,foto_area">Menyimpan...</span>
            </flux:button>
        </div>
    </x-tabs-wpi.layout>
</section>