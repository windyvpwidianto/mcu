<div x-data="{ open: @entangle('isOpen') }" 
     x-show="open" 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm transition-opacity"
     style="display: none;"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden" 
         @click.outside="open = false"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        
        <!-- Header -->
        <div class="px-8 py-5 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary/10 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Riwayat Aktivitas MCU</h3>
                    <p class="text-sm text-gray-500">Peserta: <span class="font-semibold text-gray-700">{{ $employee ? $employee->name : '-' }}</span></p>
                </div>
            </div>
            <button @click="open = false" wire:click="closeModal" class="btn btn-sm btn-circle btn-ghost text-gray-400 hover:text-gray-700 hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Body / Timeline -->
        <div class="p-8 overflow-y-auto flex-1 bg-gray-50/30">
            @if($activities->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <div class="bg-gray-100 p-4 rounded-full mb-4">
                        <svg class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="text-lg font-medium text-gray-500">Belum ada aktivitas tercatat</p>
                    <p class="text-sm mt-1 text-gray-400">Aktivitas MCU untuk peserta ini akan muncul di sini.</p>
                </div>
            @else
                <div class="flex flex-col">
                    @foreach($activities as $index => $activity)
                        <div class="flex">
                            <!-- Desktop Timestamp -->
                            <div class="hidden md:block w-32 shrink-0 text-right pr-6 pt-1">
                                <div class="text-sm font-bold text-gray-800">{{ $activity->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500 font-mono mt-0.5">{{ $activity->created_at->format('H:i:s') }}</div>
                            </div>

                            <!-- Line & Dot -->
                            <div class="flex flex-col items-center w-6 shrink-0 mr-4 md:mr-6">
                                <div class="relative z-10 h-6 w-6 rounded-full bg-primary/20 flex items-center justify-center shrink-0 mt-0.5">
                                    <div class="h-3 w-3 rounded-full bg-primary"></div>
                                </div>
                                @if($index !== count($activities) - 1)
                                    <div class="w-0.5 bg-primary/20 flex-1 my-1 min-h-[40px]"></div>
                                @endif
                            </div>

                            <!-- Content Card -->
                            <div class="flex-1 pb-8">
                                <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden hover:shadow-md transition-shadow">
                                    <div class="p-4 sm:p-5 border-b border-gray-50 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="md:hidden mb-1">
                                            <div class="text-xs font-bold text-gray-800">{{ $activity->created_at->format('d M Y, H:i:s') }}</div>
                                        </div>
                                        <div class="font-semibold text-gray-800 text-base">
                                            {{ $activity->description }}
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs font-medium text-gray-500 bg-white px-2.5 py-1 rounded-full border border-gray-100 shadow-sm w-fit">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            {{ $activity->causer ? $activity->causer->name : 'System' }}
                                        </div>
                                    </div>
                                    
                                    @if($activity->properties->isNotEmpty())
                                        <div class="p-4 sm:p-5">
                                            @php
                                                // Helper function to format values beautifully
                                                if (!function_exists('formatActivityValue')) {
                                                    function formatActivityValue($val, $k) {
                                                        if ($val === null || $val === '') return '-';
                                                        if (is_array($val)) return json_encode($val);
                                                        if ($val === true) return 'Ya';
                                                        if ($val === false) return 'Tidak';
                                                        if (in_array($k, ['status', 'medical_status', 'fit_status'])) {
                                                            return ucwords(str_replace('_', ' ', $val));
                                                        }
                                                        if (is_string($val) && preg_match('/^\d{4}-\d{2}-\d{2}/', $val)) {
                                                            try {
                                                                return \Carbon\Carbon::parse($val)->translatedFormat('d M Y');
                                                            } catch (\Exception $e) {}
                                                        }
                                                        return $val;
                                                    }
                                                }
                                            @endphp

                                            @if(isset($activity->properties['old']) && isset($activity->properties['attributes']))
                                                {{-- Diffs Display --}}
                                                <div class="overflow-x-auto rounded-lg border border-gray-100">
                                                    <table class="w-full text-left text-sm border-collapse">
                                                        <thead>
                                                            <tr class="bg-gray-50/80 border-b border-gray-100">
                                                                <th class="py-2.5 px-4 text-gray-500 font-medium w-1/4">Atribut</th>
                                                                <th class="py-2.5 px-4 text-gray-500 font-medium w-3/8">Nilai Lama</th>
                                                                <th class="py-2.5 px-4 text-gray-500 font-medium w-3/8">Nilai Baru</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-50">
                                                            @foreach($activity->properties['attributes'] as $key => $newValueRaw)
                                                                @php
                                                                    $oldValueRaw = $activity->properties['old'][$key] ?? null;
                                                                    
                                                                    // Sembunyikan atribut sistem yang tidak relevan bagi user
                                                                    if (in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at', 'mcu_record_id', 'employee_id'])) continue;
                                                                    
                                                                    // Sembunyikan jika tidak ada perubahan, kecuali atribut kunci
                                                                    if ($oldValueRaw === $newValueRaw && !in_array($key, ['status', 'medical_status', 'mcu_date'])) continue; 
                                                                    
                                                                    $oldFormatted = formatActivityValue($oldValueRaw, $key);
                                                                    $newFormatted = formatActivityValue($newValueRaw, $key);
                                                                @endphp
                                                                <tr class="hover:bg-gray-50/30 transition-colors">
                                                                    <td class="py-3 px-4 font-medium text-gray-700 capitalize">
                                                                        {{ str_replace('_', ' ', $key) }}
                                                                    </td>
                                                                    <td class="py-3 px-4 text-gray-500">
                                                                        @if($oldValueRaw !== $newValueRaw && $oldValueRaw !== null)
                                                                            <span class="inline-block px-2 py-1 bg-red-50 text-red-600 rounded line-through decoration-red-300">
                                                                                {{ $oldFormatted }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-gray-400 italic">
                                                                                {{ $oldFormatted }}
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="py-3 px-4">
                                                                        @if($oldValueRaw !== $newValueRaw)
                                                                            <span class="inline-block px-2 py-1 bg-emerald-50 text-emerald-700 font-medium rounded border border-emerald-100">
                                                                                {{ $newFormatted }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-gray-700">
                                                                                {{ $newFormatted }}
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                {{-- Custom Properties Display --}}
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    @foreach($activity->properties as $key => $value)
                                                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                                                            <div class="text-xs text-gray-500 font-medium capitalize mb-1">{{ str_replace('_', ' ', $key) }}</div>
                                                            <div class="font-semibold text-gray-800 text-sm">
                                                                {{ formatActivityValue($value, $key) }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="px-8 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
            <button @click="open = false" wire:click="closeModal" class="btn btn-outline border-gray-300 text-gray-700 hover:bg-gray-100 hover:border-gray-400">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>
