<section class="w-full px-4 mx-auto pb-24 lg:pb-8 sm:px-6 lg:px-8">
    <x-toast />

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 mb-6 border-b border-base-200 gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-primary/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold md:text-2xl text-base-content">MCU Roster</h2>
                <p class="text-sm text-base-content/60">Kelola daftar peserta dan riwayat MCU Karyawan.</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <!-- Tombol kustom yang men-trigger modal add-people -->
            <button x-data @click="$dispatch('open-my-modal')" class="btn btn-outline btn-primary btn-sm md:btn-md gap-2 shadow-sm hover:shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Peserta
            </button>
            
            <!-- Component diletakkan di sini tapi tombol aslinya kita sembunyikan via CSS (hanya modalnya yang dirender) -->
            <style>
                .hide-add-btn > section > label.btn-info {
                    display: none !important;
                }
            </style>
            <div class="hide-add-btn">
                <livewire:people.add-people />
            </div>
            @if(auth()->user()->hasRole('administrator') || auth()->user()->hasRole('medical staff'))
            <button wire:click="openImportModal" class="btn btn-primary btn-sm md:btn-md gap-2 shadow-sm hover:shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                Import Data Historis
            </button>
            @endif
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="grid items-center grid-cols-1 gap-4 mb-6 md:grid-cols-4 lg:grid-cols-5">
        <div class="md:col-span-2 lg:col-span-2">
            <x-form.input-floating label="Cari Karyawan (Nama / NIK)..." model="search" />
        </div>
        <div>
            <select wire:model.live="filterDepartment" class="select select-bordered w-full select-md">
                <option value="">Semua Department</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="filterYear" class="select select-bordered w-full select-md">
                <option value="">Semua Tahun MCU</option>
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Daftar Karyawan -->
    <div class="border shadow-sm card bg-base-100 border-base-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full table-zebra">
                <thead class="bg-base-200 text-base-content/80">
                    <tr>
                        <th>Karyawan</th>
                        <th>Department</th>
                        <th>MCU Terakhir</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        @php
                            $latestMcu = $employee->mcuRecords->first();
                        @endphp
                        <tr class="hover">
                            <td>
                                <div class="flex items-center space-x-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content rounded-full w-10">
                                            <span>{{ $employee->initials() }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-bold">{{ $employee->name }}</div>
                                        <div class="text-xs font-mono opacity-60">NIK: {{ $employee->employee_id ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="badge badge-ghost badge-sm">{{ $employee->department_name ?? '-' }}</div>
                            </td>
                            <td>
                                @if($latestMcu)
                                    <div class="font-semibold">{{ $latestMcu->mcu_year ?? '-' }}</div>
                                    <div class="text-xs badge {{ $latestMcu->status == 'Completed' ? 'badge-success' : 'badge-warning' }} badge-outline mt-1">{{ $latestMcu->status }}</div>
                                @else
                                    <span class="text-xs text-base-content/40 italic">Belum ada riwayat</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('mcu.employee-detail', $employee->id) }}" class="btn btn-sm btn-ghost hover:bg-primary/20 hover:text-primary">
                                    Lihat Detail
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-base-content/50">
                                Belum ada data karyawan atau tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-base-200 bg-base-50">
            {{ $employees->links() }}
        </div>
    </div>

    <!-- Modal Import Excel -->
    @if ($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in-down">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">
                
                <!-- Header Modal -->
                <div class="flex justify-between items-center p-5 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Import Jadwal MCU via Excel
                    </h3>
                    <button wire:click="closeImportModal" class="btn btn-sm btn-circle btn-ghost text-gray-500 hover:bg-gray-100">✕</button>
                </div>

                <!-- Body Modal -->
                <div class="p-5 overflow-y-auto custom-scrollbar flex-1">
                    
                    @if(is_null($importResults))
                        <div class="alert alert-info bg-blue-50 text-blue-800 border-blue-200 mb-5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <h3 class="font-bold">Ketentuan Format Excel:</h3>
                                <div class="text-sm">
                                    Minimal memiliki 3 kolom (baris pertama sebagai header):
                                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                                        <li><span class="font-mono bg-blue-100 px-1 rounded">employee_id</span> (NIK Karyawan)</li>
                                        <li><span class="font-mono bg-blue-100 px-1 rounded">tanggal_mcu</span> (Format: YYYY-MM-DD atau cell date)</li>
                                        <li><span class="font-mono bg-blue-100 px-1 rounded">lokasi_mcu</span> (Nama klinik/RS - Opsional)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="form-control w-full">
                            <label class="label">
                                <span class="label-text font-semibold text-gray-700">Pilih File Excel (.xls, .xlsx)</span>
                            </label>
                            <input type="file" wire:model="excelFile" class="file-input file-input-bordered file-input-primary w-full bg-gray-50" accept=".xls,.xlsx" />
                            @error('excelFile') 
                                <span class="text-error text-sm mt-2 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    {{ $message }}
                                </span> 
                            @enderror
                        </div>

                        <div wire:loading wire:target="excelFile" class="mt-3 text-sm text-info flex items-center gap-2">
                            <span class="loading loading-spinner loading-sm"></span> Mengunggah file...
                        </div>
                    @else
                        <!-- Tampilan Hasil Import -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-green-50 p-4 rounded-xl border border-green-100 text-center shadow-sm">
                                <div class="text-2xl font-bold text-green-600">{{ $importResults['success'] }}</div>
                                <div class="text-xs text-green-700 uppercase tracking-wide mt-1">Sukses</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-xl border border-red-100 text-center shadow-sm">
                                <div class="text-2xl font-bold text-red-600">{{ $importResults['failed'] }}</div>
                                <div class="text-xs text-red-700 uppercase tracking-wide mt-1">Gagal</div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 text-center shadow-sm">
                                <div class="text-2xl font-bold text-blue-600">{{ $importResults['new_schedules'] }}</div>
                                <div class="text-xs text-blue-700 uppercase tracking-wide mt-1">Jadwal Baru</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-xl border border-purple-100 text-center shadow-sm">
                                <div class="text-2xl font-bold text-purple-600">{{ $importResults['new_participants'] }}</div>
                                <div class="text-xs text-purple-700 uppercase tracking-wide mt-1">Peserta Baru</div>
                            </div>
                        </div>

                        @if(count($importErrors) > 0)
                            <div class="mt-2">
                                <h4 class="font-bold text-red-600 mb-3 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Daftar Baris Error ({{ count($importErrors) }})
                                </h4>
                                <div class="overflow-x-auto border border-red-100 rounded-lg shadow-inner bg-red-50/30 max-h-60">
                                    <table class="table table-sm table-pin-rows">
                                        <thead>
                                            <tr class="bg-red-100 text-red-800">
                                                <th class="w-16">Baris</th>
                                                <th class="w-32">Employee ID</th>
                                                <th>Alasan Gagal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($importErrors as $error)
                                                <tr class="hover:bg-red-50">
                                                    <td class="font-mono text-center">{{ $error['row'] }}</td>
                                                    <td class="font-mono">{{ $error['employee_id'] }}</td>
                                                    <td class="text-sm text-red-700">{{ $error['reason'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Footer Modal -->
                <div class="p-5 border-t border-gray-100 bg-gray-50 flex justify-end gap-3 rounded-b-2xl">
                    <button wire:click="closeImportModal" class="btn btn-outline border-gray-300 text-gray-700 hover:bg-gray-200 hover:border-gray-400">Tutup</button>
                    
                    @if(is_null($importResults))
                        <button wire:click="importExcel" class="btn btn-primary shadow-md hover:shadow-lg transition-all" wire:loading.attr="disabled" {{ empty($excelFile) ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="importExcel">Mulai Import</span>
                            <span wire:loading wire:target="importExcel" class="flex items-center gap-2">
                                <span class="loading loading-spinner loading-sm"></span> Memproses...
                            </span>
                        </button>
                    @else
                        <button wire:click="resetImport" class="btn btn-primary shadow-md hover:shadow-lg transition-all">
                            Import File Lain
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</section>
