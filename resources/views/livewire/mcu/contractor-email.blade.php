<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Email Notifikasi Kontraktor</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Kelola alamat email resmi PIC kontraktor yang akan menerima notifikasi jadwal & expired MCU</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="$refresh" class="btn btn-sm btn-outline border-gray-300 hover:bg-gray-100 text-gray-700 normal-case rounded-lg flex items-center gap-2">
                <svg wire:loading.class="animate-spin" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Segarkan</span>
            </button>
        </div>
    </div>

    <!-- Flash Message -->
    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm animate-fade-in">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 font-bold">&times;</button>
        </div>
    @endif

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Kontraktor</p>
                <h3 class="text-2xl font-extrabold text-gray-800 mt-1">{{ $stats['total'] }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $stats['active'] }} status aktif</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        </div>

        <div wire:click="$set('filterEmail', 'has_email')" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between cursor-pointer transition hover:border-emerald-300 {{ $filterEmail === 'has_email' ? 'ring-2 ring-emerald-500' : '' }}">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Email Terisi</p>
                <h3 class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $stats['with_email'] }}</h3>
                <p class="text-xs text-emerald-600/80 mt-1">Siap kirim notifikasi</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
        </div>

        <div wire:click="$set('filterEmail', 'no_email')" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between cursor-pointer transition hover:border-amber-300 {{ $filterEmail === 'no_email' ? 'ring-2 ring-amber-500' : '' }}">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Belum Ada Email</p>
                <h3 class="text-2xl font-extrabold text-amber-700 mt-1">{{ $stats['without_email'] }}</h3>
                <p class="text-xs text-amber-600/80 mt-1">Perlu diatur</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <div wire:click="$set('filterEmail', 'all')" class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between cursor-pointer transition hover:border-blue-300 {{ $filterEmail === 'all' ? 'ring-2 ring-blue-500' : '' }}">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Cakupan Konfigurasi</p>
                @php
                    $percentage = $stats['total'] > 0 ? round(($stats['with_email'] / $stats['total']) * 100) : 0;
                @endphp
                <h3 class="text-2xl font-extrabold text-blue-700 mt-1">{{ $percentage }}%</h3>
                <div class="w-28 bg-gray-200 h-1.5 rounded-full mt-2 overflow-hidden">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Search Bar -->
            <div class="relative flex-1">
                <input 
                    type="text" 
                    id="searchContractorInput"
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Cari nama kontraktor atau alamat email..." 
                    class="input input-bordered w-full pl-10 bg-gray-50/50 border-gray-200 focus:bg-white focus:border-emerald-500 rounded-xl text-sm"
                />
                <svg class="w-5 h-5 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                @if(!empty($search))
                    <button wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs bg-gray-200 hover:bg-gray-300 rounded-full w-5 h-5 flex items-center justify-center">
                        ✕
                    </button>
                @endif
            </div>

            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-gray-500">Filter Email:</label>
                    <select wire:model.live="filterEmail" class="select select-bordered select-sm border-gray-200 rounded-lg text-xs bg-gray-50 focus:bg-white">
                        <option value="all">Semua Status Email</option>
                        <option value="has_email">Sudah Ada Email</option>
                        <option value="no_email">Belum Ada Email</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-gray-500">Status Vendor:</label>
                    <select wire:model.live="filterStatus" class="select select-bordered select-sm border-gray-200 rounded-lg text-xs bg-gray-50 focus:bg-white">
                        <option value="all">Semua Status</option>
                        <option value="enabled">Aktif (Enabled)</option>
                        <option value="disabled">Non-aktif (Disabled)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        <th class="py-4 px-6 text-center w-16">No</th>
                        <th class="py-4 px-6">Nama Kontraktor</th>
                        <th class="py-4 px-6">Alamat Email Notifikasi MCU</th>
                        <th class="py-4 px-6 text-center">Status Email</th>
                        <th class="py-4 px-6 text-center">Pekerja MCU</th>
                        <th class="py-4 px-6 text-center">Status Vendor</th>
                        <th class="py-4 px-6 text-right w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contractors as $index => $contractor)
                        <tr class="hover:bg-gray-50/60 transition">
                            <!-- No -->
                            <td class="py-4 px-6 text-center text-gray-400 font-mono text-xs">
                                {{ $contractors->firstItem() + $index }}
                            </td>

                            <!-- Nama Kontraktor -->
                            <td class="py-4 px-6 font-semibold text-gray-800">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center font-bold text-xs uppercase flex-shrink-0">
                                        {{ substr($contractor->contractor_name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">{{ $contractor->contractor_name }}</div>
                                        <div class="text-xs text-gray-400">ID #{{ $contractor->id }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Alamat Email -->
                            <td class="py-4 px-6">
                                @if(!empty($contractor->email))
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <a href="mailto:{{ $contractor->email }}" class="font-medium text-emerald-700 hover:text-emerald-900 hover:underline">
                                            {{ $contractor->email }}
                                        </a>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2 text-gray-400 italic text-xs">
                                        <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>Belum diatur</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Status Email Badge -->
                            <td class="py-4 px-6 text-center">
                                @if(!empty($contractor->email))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Kosong
                                    </span>
                                @endif
                            </td>

                            <!-- Pekerja Terdaftar -->
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 font-bold text-xs">
                                    {{ $contractor->users_count }} Orang
                                </span>
                            </td>

                            <!-- Status Vendor -->
                            <td class="py-4 px-6 text-center">
                                @if($contractor->status === 'enabled')
                                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Aktif</span>
                                @else
                                    <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded-md">Non-aktif</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button 
                                        wire:click="openEditModal({{ $contractor->id }})" 
                                        class="btn btn-xs btn-primary normal-case font-semibold rounded-lg shadow-sm flex items-center gap-1"
                                        title="Atur Alamat Email"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span>{{ !empty($contractor->email) ? 'Ubah' : 'Atur Email' }}</span>
                                    </button>

                                    @if(!empty($contractor->email))
                                        <button 
                                            wire:click="clearEmail({{ $contractor->id }})"
                                            wire:confirm="Yakin ingin menghapus email notifikasi untuk {{ $contractor->contractor_name }}?" 
                                            class="btn btn-xs btn-ghost text-red-500 hover:bg-red-50 rounded-lg p-1"
                                            title="Hapus Email"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-sm font-semibold text-gray-600">Tidak ada kontraktor yang cocok dengan kriteria pencarian.</p>
                                    <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci lain atau ubah filter status.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-100 flex items-center justify-between">
            <div class="text-xs text-gray-500">
                Menampilkan <span class="font-bold text-gray-800">{{ $contractors->firstItem() ?? 0 }}</span> - <span class="font-bold text-gray-800">{{ $contractors->lastItem() ?? 0 }}</span> dari total <span class="font-bold text-gray-800">{{ $contractors->total() }}</span> kontraktor
            </div>
            <div>
                {{ $contractors->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Form Edit Email -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm animate-fade-in">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
                <!-- Modal Header -->
                <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/70">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-100 text-emerald-700 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-base">Atur Email PIC Kontraktor</h3>
                            <p class="text-xs text-gray-500">Notifikasi MCU akan dikirim ke alamat ini</p>
                        </div>
                    </div>
                    <button wire:click="closeEditModal" class="btn btn-sm btn-circle btn-ghost text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <!-- Modal Body -->
                <form wire:submit.prevent="saveEmail">
                    <div class="p-6 space-y-4">
                        <!-- Info Kontraktor -->
                        <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="text-xs font-semibold uppercase text-gray-400">Nama Perusahaan Kontraktor</div>
                            <div class="text-sm font-bold text-gray-800 mt-0.5">{{ $selectedContractorName }}</div>
                        </div>

                        <!-- Input Email -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1.5 uppercase tracking-wider">
                                Alamat Email PIC / HSE <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                wire:model="email" 
                                placeholder="contoh: safety.pic@perusahaan.com"
                                class="input input-bordered w-full rounded-xl text-sm focus:border-emerald-500 @error('email') border-red-500 ring-1 ring-red-500 @enderror"
                                autofocus
                            />
                            @error('email')
                                <p class="text-xs text-red-500 mt-1 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="text-xs text-gray-400 mt-2">
                                💡 Seluruh notifikasi terkait pekerja kontraktor ini (pengingat jadwal H-30/H-7/H-3, rekapitulasi, dan masa berlaku MCU) akan dikirimkan ke email ini.
                            </p>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="p-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-end gap-2">
                        <button type="button" wire:click="closeEditModal" class="btn btn-sm btn-ghost normal-case text-gray-600 rounded-xl">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary normal-case rounded-xl shadow-sm px-5 flex items-center gap-1.5">
                            <span wire:loading.remove wire:target="saveEmail">Simpan Email</span>
                            <span wire:loading wire:target="saveEmail" class="flex items-center gap-1">
                                <svg class="animate-spin w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
