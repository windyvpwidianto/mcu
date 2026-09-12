<section class="w-full px-4 mx-auto pb-24 lg:pb-8 sm:px-6 lg:px-8">
    <x-toast />

    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('mcu.generate') }}" class="btn btn-ghost btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke MCU Roster
        </a>
    </div>

    <!-- Header / Employee Info -->
    <div class="card bg-base-100 border border-base-200 shadow-sm mb-6">
        <div class="card-body p-6 flex flex-col md:flex-row gap-6 items-center md:items-start">
            <div class="avatar placeholder">
                <div class="bg-neutral text-neutral-content rounded-full w-24">
                    <span class="text-3xl">{{ $employee->initials() }}</span>
                </div>
            </div>
            <div class="flex-1 text-center md:text-left">
                <h2 class="text-2xl font-bold">{{ $employee->name }}</h2>
                <div class="text-base-content/60 font-mono mt-1">NIK: {{ $employee->employee_id ?? '-' }}</div>
                
                <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-4">
                    <div class="badge badge-primary">{{ $employee->department_name ?? 'No Department' }}</div>
                    <div class="badge badge-outline">{{ $employee->pilih_divisi ?? 'No Division' }}</div>
                </div>
            </div>
            <div>
                <button wire:click="openAddModal" class="btn btn-primary shadow hover:-translate-y-0.5 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Catat Riwayat MCU
                </button>
            </div>
        </div>
    </div>

    <!-- MCU History List -->
    <div class="card bg-base-100 border border-base-200 shadow-sm">
        <div class="card-body p-0">
            <div class="p-4 border-b border-base-200 bg-base-200/30 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="text-lg font-bold">Riwayat MCU</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-base-50">
                        <tr>
                            <th>Tahun</th>
                            <th>Tanggal Pelaksanaan</th>
                            <th>Status Kehadiran</th>
                            <th>Hasil Medis</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employee->mcuRecords as $record)
                            <tr class="hover">
                                <td class="font-bold text-lg">{{ $record->mcu_year }}</td>
                                <td>{{ \Carbon\Carbon::parse($record->mcu_date)->translatedFormat('d F Y') }}</td>
                                <td>
                                    <div class="badge {{ $record->status == 'Completed' ? 'badge-success' : 'badge-warning' }} badge-outline">
                                        {{ $record->status }}
                                    </div>
                                </td>
                                <td>
                                    @if($record->result)
                                        @php
                                            $medColor = match($record->result->status) {
                                                'fit_to_work' => 'text-success',
                                                'fit_with_notes' => 'text-warning',
                                                'temporary_unfit' => 'text-error',
                                                'unfit' => 'text-error',
                                                default => 'text-base-content/50'
                                            };
                                            $medText = ucwords(str_replace('_', ' ', $record->result->status ?? 'Pending'));
                                        @endphp
                                        <span class="font-semibold {{ $medColor }}">{{ $medText }}</span>
                                    @else
                                        <span class="text-base-content/40 italic">Menunggu Hasil</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-sm btn-ghost hover:bg-primary/10 hover:text-primary">Lihat Medis</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-8 text-base-content/50">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Belum ada riwayat MCU untuk karyawan ini.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Riwayat MCU -->
    @if ($showAddModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl flex flex-col">
                <div class="flex justify-between items-center p-5 border-b border-gray-100">
                    <h3 class="text-lg font-bold">Catat Riwayat MCU</h3>
                    <button wire:click="closeAddModal" class="btn btn-sm btn-circle btn-ghost">✕</button>
                </div>
                <div class="p-5 space-y-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Tahun MCU</span></label>
                        <input type="number" wire:model="mcu_year" class="input input-bordered" />
                        @error('mcu_year') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Tanggal Pelaksanaan</span></label>
                        <input type="date" wire:model="mcu_date" class="input input-bordered" />
                        @error('mcu_date') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Status Kehadiran</span></label>
                        <select wire:model="status" class="select select-bordered">
                            <option value="Completed">Selesai / Hadir</option>
                            <option value="Absent">Tidak Hadir</option>
                            <option value="Pending">Menunggu Pelaksanaan</option>
                        </select>
                        @error('status') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Kesimpulan Medis Dasar</span></label>
                        <select wire:model="medical_status" class="select select-bordered">
                            <option value="fit_to_work">Fit To Work</option>
                            <option value="fit_with_notes">Fit With Notes</option>
                            <option value="temporary_unfit">Temporary Unfit</option>
                            <option value="unfit">Unfit</option>
                        </select>
                        @error('medical_status') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="p-5 border-t border-gray-100 flex justify-end gap-3 bg-gray-50 rounded-b-2xl">
                    <button wire:click="closeAddModal" class="btn btn-outline">Batal</button>
                    <button wire:click="saveMcuRecord" class="btn btn-primary">Simpan Riwayat</button>
                </div>
            </div>
        </div>
    @endif
</section>
