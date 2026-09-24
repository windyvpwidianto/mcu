<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Mendatang & Notifikasi</h2>
            <p class="text-sm text-gray-500 mt-1">Pantau dan kelola jadwal Medical Check-Up tahunan berikutnya.</p>
        </div>
        
        <div class="mt-4 md:mt-0 flex flex-col md:flex-row items-start md:items-center gap-3">
            <div class="px-3 py-2 bg-blue-50 text-blue-700 text-sm font-semibold rounded-lg border border-blue-100 flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Hari ini: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') }}
            </div>
            <button wire:click="$refresh" class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Refresh Data
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div wire:click="$set('filterPeriod', 'all')" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 cursor-pointer transition-all hover:shadow-md hover:border-blue-200 {{ $filterPeriod === 'all' ? 'ring-2 ring-blue-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Karyawan (MCU)</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['all'] }}</p>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>

        <div wire:click="$set('filterPeriod', 'next_30')" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 cursor-pointer transition-all hover:shadow-md hover:border-yellow-200 {{ $filterPeriod === 'next_30' ? 'ring-2 ring-yellow-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Mendatang (30 Hari)</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['next_30'] }}</p>
                </div>
                <div class="p-3 bg-yellow-50 text-yellow-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>

        <div wire:click="$set('filterPeriod', 'next_7')" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 cursor-pointer transition-all hover:shadow-md hover:border-orange-200 {{ $filterPeriod === 'next_7' ? 'ring-2 ring-orange-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Mendatang (7 Hari)</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['next_7'] }}</p>
                </div>
                <div class="p-3 bg-orange-50 text-orange-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <div wire:click="$set('filterPeriod', 'overdue')" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 cursor-pointer transition-all hover:shadow-md hover:border-red-200 {{ $filterPeriod === 'overdue' ? 'ring-2 ring-red-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Overdue (Terlewat)</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['overdue'] }}</p>
                </div>
                <div class="p-3 bg-red-50 text-red-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-1/3">
                    <label class="sr-only">Cari</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari Nama / ID Badge...">
                    </div>
                </div>
                <div class="w-full md:w-1/4">
                    <select wire:model.live="filterDepartment" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-1/4">
                    <select wire:model.live="filterContractor" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-lg">
                        <option value="">Semua Kontraktor</option>
                        @foreach($contractors as $contractor)
                            <option value="{{ $contractor->id }}">{{ $contractor->contractor_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-1/6">
                    <button wire:click="$set('search', ''); $set('filterDepartment', ''); $set('filterContractor', ''); $set('filterPeriod', 'all')" class="w-full bg-gray-100 text-gray-700 py-2 px-4 border border-transparent rounded-lg text-sm font-medium hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe / Area</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal MCU Tahunan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($users as $user)
                        @php
                            $nextDate = \Carbon\Carbon::parse($user->next_mcu_date);
                            $diff = \Carbon\Carbon::today()->diffInDays($nextDate, false);
                            $statusColor = 'bg-gray-100 text-gray-800';
                            $statusText = 'Aman';
                            
                            if ($diff < 0) {
                                $statusColor = 'bg-red-100 text-red-800';
                                $statusText = 'Overdue (' . abs($diff) . ' hari)';
                            } elseif ($diff === 0) {
                                $statusColor = 'bg-blue-100 text-blue-800 ring-1 ring-blue-500';
                                $statusText = 'Hari Ini';
                            } elseif ($diff <= 7) {
                                $statusColor = 'bg-orange-100 text-orange-800';
                                $statusText = 'H-' . $diff;
                            } elseif ($diff <= 30) {
                                $statusColor = 'bg-yellow-100 text-yellow-800';
                                $statusText = 'H-' . $diff;
                            } else {
                                $statusColor = 'bg-green-100 text-green-800';
                                $statusText = '> 30 Hari';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold">
                                            {{ substr($user->name, 0, 2) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">ID Badge: {{ $user->employee_id ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->contractors->isNotEmpty())
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 mb-1">
                                        Kontraktor
                                    </span>
                                    <div class="text-sm text-gray-500">{{ $user->contractors->first()->contractor_name }}</div>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 mb-1">
                                        Internal
                                    </span>
                                    @if($user->departments->isNotEmpty())
                                        <div class="text-sm text-gray-500">{{ $user->departments->first()->department_name }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $nextDate->translatedFormat('d F Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $nextDate->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('mcu.employee', $user->id) }}" class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-md">Detail & Input Hasil</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <p class="text-base font-medium">Tidak ada data ditemukan</p>
                                <p class="text-sm mt-1">Coba sesuaikan filter pencarian Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $users->links() }}
        </div>
    </div>
</div>
