<section class="w-full">
    <x-toast />

    <x-tabs-equipment.layout heading="Equipment Master"
        subheading="Manage equipment records including types, locations, and technical specifications.">
        <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-3">
            {{-- KOLOM KIRI: FORM INPUT & IMPORT --}}
            <div class="space-y-6">
                {{-- Form Manual --}}
                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow">
                    <h3 class="mb-4 font-bold">{{ $isEdit ? 'Edit Alat' : 'Tambah Alat Baru' }}</h3>

                    <div class="space-y-3">
                        <x-form.label label="Jenis Alat" />
                        <select wire:model.live="type"
                            class="w-full select select-bordered select-xs focus-within:outline-none focus-within:border-info focus-within:ring-0">
                            <option value="">-- Pilih --</option>
                            @foreach ($available_types as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>

                        <x-form.searchable-dropdown label="Area" required modelsearch="searchLocation"
                            modelid="location_id" :options="$locations" :showdropdown="$show_location" clickaction="selectLocation"
                            namedb="name" />

                        <x-form.input-floating label="Lokasi Spesifik" model="specific_location" />

                        <div class="p-3 mt-4 border rounded bg-gray-50">
                            <p class="mb-2 text-xs font-bold text-info">Spesifikasi (Otomatis dari Master)</p>

                            {{-- List Spesifikasi --}}
                            <div class="mb-3 space-y-2">
                                @forelse ($technical_data as $key => $val)
                                {{-- Gunakan md5 dari key agar ID elemen benar-benar unik dan stabil --}}
                                <div wire:key="field-{{ md5($key) }}"
                                    class="flex flex-col gap-1 p-2 bg-white border rounded shadow-sm">
                                    <div class="flex items-center justify-between">

                                        <x-form.input-floating {{-- KEMBALIKAN LABEL KE SEMULA (Ganti underscore jadi spasi lagi) --}}
                                            label="{{ str_replace('_', ' ', $key) }}" {{-- Gunakan .blur agar sinkronisasi hanya terjadi saat pindah input --}}
                                            wire:model.blur="technical_data.{{ $key }}" />

                                        <button type="button"
                                            wire:click="removeTechnicalField('{{ $key }}')"
                                            class="btn btn-xs btn-error btn-soft">
                                            <x-icon.delete />
                                        </button>
                                    </div>
                                </div>
                                @empty
                                <p class="text-xs italic font-semibold text-center text-gray-400">Pilih jenis alat...</p>
                                @endforelse
                            </div>

                            <hr class="my-2 border-gray-200">

                            {{-- Tambah Manual Jika Diperlukan --}}
                            <p class="mb-1 text-[9px] font-bold text-gray-400 uppercase">Tambah Field Manual</p>
                            <div class="flex gap-1">
                                <input type="text" wire:model="newKey" placeholder="Label"
                                    class="w-1/2 input input-xs input-bordered">
                                <input type="text" wire:model="newValue" placeholder="Value"
                                    class="w-1/2 input input-xs input-bordered">
                                <button wire:click="addTechnicalField" class="btn btn-xs btn-primary">+</button>
                            </div>
                        </div>

                        <div class="flex gap-2 mt-4">
                            <button wire:click="save" class="flex-1 btn btn-success btn-soft btn-xs">Simpan</button>
                            @if ($isEdit)
                            <button wire:click="resetForm" class="btn btn-error btn-soft btn-sm">Batal</button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- FITUR IMPORT EXCEL --}}
                <div class="p-4 border-2 border-gray-300 border-dashed rounded-lg shadow-sm bg-gray-50">
                    <h4 class="mb-2 text-sm font-bold">Import dari Excel</h4>
                    <p class="text-[10px] text-gray-500 mb-3 uppercase tracking-wider leading-tight">
                        Pilih <b>Jenis Alat</b> & <b>Lokasi</b> di atas. Data akan diambil dari sheet:
                        <b>{{ $type ?: '...' }}</b>
                    </p>

                    <div class="flex flex-col gap-2">
                        <input type="file" wire:model="file_excel"
                            class="w-full file-input file-input-bordered file-input-info file-input-xs" />

                        <div wire:loading.remove.class='hidden' wire:target="file_excel"
                            class="text-[10px] text-blue-600 animate-pulse hidden">
                            Sedang mengunggah file...
                        </div>

                        {{-- Ganti fungsi ke previewExcel --}}
                        <button wire:click="previewExcel" wire:loading.attr="disabled" @disabled(!$file_excel || !$type || !$location_id)
                            class="w-full btn btn-xs btn-outline btn-info">
                            🔍 Preview Data (Sheet: {{ $type }})
                        </button>

                        @if (!$type || !$location_id)
                        <span class="text-[9px] text-error italic text-center">* Jenis alat & lokasi wajib
                            diisi</span>
                        @endif
                    </div>

                    {{-- MODAL / SECTION PREVIEW --}}
                    {{-- Hidden Checkbox untuk Trigger Modal (Opsional jika ingin kontrol via label) --}}
                    <input type="checkbox" id="import_preview_modal" class="modal-toggle"
                        {{ $showPreview ? 'checked' : '' }} />

                    <div class="modal {{ $showPreview ? 'modal-open' : '' }}" role="dialog">
                        <div class="w-11/12 max-w-5xl modal-box"> {{-- Ukuran modal diperlebar (max-w-5xl) agar tabel data teknis tidak sesak --}}
                            <h3 class="flex items-center gap-2 text-lg font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-info" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="Path d=" M9
                                        12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414
                                        5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Konfirmasi Import Data
                            </h3>

                            <p class="py-2 text-sm text-gray-500">
                                Ditemukan <strong>{{ count($previewData) }}</strong> baris pada kategori
                                <strong>{{ $type }}</strong>. Silakan periksa kembali sebelum menyimpan.
                            </p>

                            @if (count($previewData) > 0)
                            <div class="mt-4 overflow-hidden border rounded-lg">
                                <div class="overflow-x-auto overflow-y-auto max-h-80"> {{-- Scroll horizontal & vertical --}}
                                    <table class="table w-full table-zebra table-xs">
                                        <thead class="sticky top-0 shadow-sm bg-base-200">
                                            <tr>
                                                @foreach (array_keys($previewData[0]) as $header)
                                                <th class="whitespace-nowrap">
                                                    {{ strtoupper(str_replace('_', ' ', $header)) }}
                                                </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach (array_slice($previewData, 0, 10) as $row)
                                            <tr>
                                                @foreach ($row as $value)
                                                <td class="whitespace-nowrap">{{ $value ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            @if (count($previewData) > 10)
                            <div class="badge badge-ghost mt-2 text-[10px] italic opacity-70">
                                * Menampilkan 10 baris pertama dari total {{ count($previewData) }} baris
                            </div>
                            @endif
                            @else
                            <div class="mt-4 alert alert-warning">
                                <span>Data tidak ditemukan atau sheet kosong.</span>
                            </div>
                            @endif

                            <div class="modal-action">
                                <button wire:click="importExcel" wire:loading.attr="disabled"
                                    class="text-white btn btn-success">
                                    <span wire:loading wire:target="importExcel" class="loading loading-spinner"></span>
                                    ✅ Simpan Ke Database
                                </button>

                                <button wire:click="$set('showPreview', false)" class="btn btn-ghost">
                                    Batal
                                </button>
                            </div>
                        </div>

                        {{-- Klik di luar modal untuk menutup --}}
                        <label class="modal-backdrop" wire:click="$set('showPreview', false)">Close</label>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: TABEL DATA --}}
            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow md:col-span-2">
                <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
                    <fieldset class="fieldset">
                        <x-form.label label="Cari Tipe Alat" />
                        <select wire:model.live="search"
                            class="w-full select select-bordered select-xs focus-within:outline-none focus-within:border-info focus-within:ring-0">
                            <option value="">-- Cari Tipe Alat --</option>
                            @foreach ($available_types as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </fieldset>
                    <x-form.searchable-dropdown label="Cari Area" modelsearch="cari_searchLocation"
                        modelid="cari_location_id" :options="$cari_locations" :showdropdown="$cari_show_location"
                        clickaction="selectCariLocation" namedb="name" />
                </div>
                <div class="overflow-x-auto ">
                    <table class="table table-xs">
                        <thead class="bg-gray-100">
                            <tr>
                                <th>Tipe</th>
                                <th>Lokasi</th>
                                <th>Spesifikasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equipments as $item)
                            <tr>
                                <td><strong>{{ $item->type }}</strong></td>
                                <td>{{ $item->location->name }} <br> <span
                                        class="text-[10px] text-gray-500">{{ $item->specific_location }}</span>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($item->technical_data as $k => $v)
                                        <span class="badge badge-ghost text-[9px]">{{ $k }}:
                                            {{ $v }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="flex gap-1">
                                    <button wire:click="edit({{ $item->id }})"
                                        class="btn btn-xs btn-soft btn-warning"> <x-icon.edit /> </button>
                                    <button onclick="confirm('Hapus?') || event.stopImmediatePropagation()"
                                        wire:click="delete({{ $item->id }})"
                                        class="btn btn-xs btn-soft btn-error"><x-icon.delete /></button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-400">Data tidak ditemukan
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $equipments->links() }}</div>
            </div>
        </div>
    </x-tabs-equipment.layout>
</section>