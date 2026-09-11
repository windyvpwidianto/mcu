<div class="p-6 bg-white rounded-lg shadow-sm border border-gray-100">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Master Data Karyawan MCU</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola daftar karyawan dan jadwal MCU tahunan secara terpusat</p>
        </div>
        <div class="flex gap-2 w-full md:w-auto">
            <div class="relative w-full md:w-64">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari NIK atau Nama..." class="input input-bordered w-full pl-10 bg-gray-50 focus:bg-white transition-colors" />
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <button wire:click="openModal" class="btn btn-primary shadow-md hover:shadow-lg transition-all duration-300 gap-2 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Data
            </button>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg border border-gray-200 shadow-sm">
        <table class="table w-full border-collapse">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-4 py-3 border-b font-semibold">ID Badge</th>
                    <th class="px-4 py-3 border-b font-semibold">Nama Karyawan</th>
                    <th class="px-4 py-3 border-b font-semibold">Perusahaan / Posisi</th>
                    <th class="px-4 py-3 border-b font-semibold">No. WhatsApp</th>
                    <th class="px-4 py-3 border-b font-semibold">Target MCU Berikutnya</th>
                    <th class="px-4 py-3 border-b font-semibold text-center w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($masterData as $data)
                <tr class="hover:bg-indigo-50/50 transition-colors duration-200">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $data->nik }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $data->employee_name }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-sm text-gray-800">{{ $data->company ?? '-' }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">{{ $data->position ?? '-' }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $data->hp_number ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @if($data->mcu_date)
                            <div class="font-medium text-indigo-700 bg-indigo-50 inline-block px-2.5 py-1 rounded-md text-sm border border-indigo-100 shadow-sm">
                                {{ $data->mcu_date->format('d M Y') }}
                            </div>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-1">
                            <button wire:click="openHistory({{ $data->id }})" class="btn btn-sm btn-ghost text-emerald-600 hover:bg-emerald-50 hover:text-emerald-800 transition-colors" title="Riwayat MCU">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                            <button wire:click="edit({{ $data->id }})" class="btn btn-sm btn-ghost text-blue-600 hover:bg-blue-100 hover:text-blue-800 transition-colors" title="Edit Data">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button wire:click="delete({{ $data->id }})" onclick="confirm('Apakah Anda yakin ingin menghapus data master MCU untuk {{ $data->employee_name }}?') || event.stopImmediatePropagation()" class="btn btn-sm btn-ghost text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors" title="Hapus Data">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center">
                            <div class="bg-gray-100 p-4 rounded-full mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            </div>
                            <p class="font-medium text-gray-600">Belum ada data MCU Master</p>
                            <p class="text-sm mt-1">Silakan klik tombol "Tambah Data" untuk memulai.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $masterData->links() }}
    </div>

    <!-- Modal Form -->
    <div class="modal {{ $showModal ? 'modal-open' : '' }}" role="dialog">
        <div class="modal-box w-11/12 max-w-3xl bg-white shadow-2xl rounded-2xl border border-gray-100">
            <button wire:click="closeModal" class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4 hover:bg-gray-100">✕</button>
            
            <h3 class="font-bold text-xl mb-6 text-gray-800 border-b pb-4 flex items-center gap-2">
                <span class="bg-primary/10 text-primary p-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </span>
                {{ $isEdit ? 'Edit Data Karyawan MCU' : 'Tambah Karyawan Baru' }}
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <!-- Kolom Kiri -->
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label font-medium pb-1"><span class="label-text text-gray-700">Nama Lengkap <span class="text-red-500">*</span></span></label>
                        <input type="text" wire:model="employee_name" class="input input-bordered w-full focus:border-primary focus:ring-1 focus:ring-primary transition-all @error('employee_name') input-error @enderror" placeholder="Cth: Budi Santoso" />
                        @error('employee_name') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label font-medium pb-1"><span class="label-text text-gray-700">ID Badge <span class="text-red-500">*</span></span></label>
                        <input type="text" wire:model="nik" class="input input-bordered w-full focus:border-primary focus:ring-1 focus:ring-primary transition-all @error('nik') input-error @enderror" placeholder="Cth: 12345678" />
                        @error('nik') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label font-medium pb-1"><span class="label-text text-gray-700">Perusahaan</span></label>
                        <input type="text" wire:model="company" class="input input-bordered w-full focus:border-primary focus:ring-1 focus:ring-primary transition-all" placeholder="Nama Perusahaan/Vendor" />
                    </div>

                    <div class="form-control">
                        <label class="label font-medium pb-1"><span class="label-text text-gray-700">Posisi / Jabatan</span></label>
                        <input type="text" wire:model="position" class="input input-bordered w-full focus:border-primary focus:ring-1 focus:ring-primary transition-all" placeholder="Jabatan Saat Ini" />
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label font-medium pb-1"><span class="label-text text-gray-700">Tanggal Lahir</span></label>
                        <input type="date" wire:model="birth_date" class="input input-bordered w-full focus:border-primary focus:ring-1 focus:ring-primary transition-all" />
                    </div>

                    <div class="form-control">
                        <label class="label font-medium pb-1"><span class="label-text text-gray-700">No. KTP</span></label>
                        <input type="text" wire:model="ktp_number" class="input input-bordered w-full focus:border-primary focus:ring-1 focus:ring-primary transition-all" placeholder="Cth: 3171xxxxxxxx" />
                    </div>

                    <div class="form-control">
                        <label class="label font-medium pb-1">
                            <span class="label-text text-gray-700">No. WhatsApp <span class="text-xs text-gray-500 font-normal ml-1">(Utk Notif)</span></span>
                        </label>
                        <input type="text" wire:model="hp_number" class="input input-bordered w-full focus:border-primary focus:ring-1 focus:ring-primary transition-all" placeholder="Cth: 628123456789" />
                        <span class="text-xs text-gray-400 mt-1">Sertakan kode negara, contoh: 628...</span>
                    </div>

                    <div class="form-control">
                        <label class="label font-medium pb-1">
                            <span class="label-text text-gray-700">Jadwal MCU <span class="text-red-500">*</span></span>
                        </label>
                        <input type="date" wire:model="mcu_date" class="input input-bordered w-full focus:border-primary focus:ring-1 focus:ring-primary transition-all bg-indigo-50/30 @error('mcu_date') input-error @enderror" />
                        @error('mcu_date') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                        <span class="text-xs text-indigo-500 mt-1">Tanggal ini akan berulang setiap tahun secara otomatis.</span>
                    </div>
                </div>
            </div>

            <div class="modal-action mt-8 pt-5 border-t border-gray-100 flex justify-end gap-2">
                <button wire:click="closeModal" class="btn btn-ghost hover:bg-gray-100">Batal</button>
                <button wire:click="save" class="btn btn-primary px-8 shadow-md hover:shadow-lg" wire:loading.attr="disabled">
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                    <svg wire:loading.remove wire:target="save" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Data
                </button>
            </div>
        </div>
        <div class="modal-backdrop bg-gray-900/40 backdrop-blur-sm" wire:click="closeModal">
            <button class="cursor-default">close</button>
        </div>
    </div>

    <!-- Modal Riwayat MCU -->
    <div class="modal {{ $showHistoryModal ? 'modal-open' : '' }}" role="dialog">
        <div class="modal-box w-11/12 max-w-2xl bg-white shadow-2xl rounded-2xl border border-gray-100">
            <button wire:click="closeHistoryModal" class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4 hover:bg-gray-100">✕</button>
            
            <h3 class="font-bold text-xl mb-6 text-gray-800 border-b pb-4 flex items-center gap-2">
                <span class="bg-emerald-100 text-emerald-600 p-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                Riwayat MCU: {{ $employeeHistoryName }}
            </h3>

            <!-- Form Tambah Riwayat Manual -->
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-6">
                <h4 class="font-semibold text-gray-700 mb-3 text-sm">Tambah Riwayat Manual (Masa Lalu)</h4>
                <div class="flex flex-col md:flex-row gap-3 items-end">
                    <div class="form-control w-full">
                        <label class="label p-0 pb-1 text-xs font-medium text-gray-600">Tanggal Pelaksanaan <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="new_history_date" class="input input-sm input-bordered w-full" />
                    </div>
                    <div class="form-control w-full">
                        <label class="label p-0 pb-1 text-xs font-medium text-gray-600">Catatan Tambahan</label>
                        <input type="text" wire:model="new_history_notes" class="input input-sm input-bordered w-full" placeholder="Cth: Hasil Fit to Work" />
                    </div>
                    <button wire:click="addHistory" class="btn btn-sm btn-primary shrink-0 px-4">Tambahkan</button>
                </div>
                @error('new_history_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <!-- List Riwayat -->
            <div class="overflow-y-auto max-h-64 pr-2">
                @if(count($histories) > 0)
                    <div class="relative border-l-2 border-emerald-200 ml-3 space-y-6">
                        @foreach($histories as $history)
                        <div class="relative pl-6">
                            <div class="absolute -left-1.5 top-1.5 w-3 h-3 bg-emerald-500 rounded-full border-2 border-white"></div>
                            <div class="bg-white p-3 rounded-lg border border-gray-100 shadow-sm flex justify-between items-start">
                                <div>
                                    <div class="font-bold text-gray-800">{{ $history->historical_date->format('d M Y') }}</div>
                                    @if($history->notes)
                                        <div class="text-sm text-gray-500 mt-1">{{ $history->notes }}</div>
                                    @endif
                                </div>
                                <button wire:click="deleteHistory({{ $history->id }})" onclick="confirm('Hapus riwayat tanggal {{ $history->historical_date->format('d M Y') }}?') || event.stopImmediatePropagation()" class="text-red-500 hover:text-red-700 p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p>Belum ada catatan riwayat MCU untuk karyawan ini.</p>
                    </div>
                @endif
            </div>
            
        </div>
        <div class="modal-backdrop bg-gray-900/40 backdrop-blur-sm" wire:click="closeHistoryModal">
            <button class="cursor-default">close</button>
        </div>
    </div>
</div>
