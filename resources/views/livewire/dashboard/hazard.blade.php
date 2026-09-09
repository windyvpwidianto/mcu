<section>
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:justify-between">
        <div>
            <h1 class="text-xl font-bold">{{ __('Hazard Report Dashboard') }}</h1>
            <p class="text-xs text-gray-600">{{ __('Ringkasan kondisi laporan hazard terkini') }}</p>
        </div>
        <div class="w-full md:max-w-xs">
            <fieldset class="fieldset">
                <x-form.label label="Rentang Tanggal" required />
                <div class="join" wire:ignore x-data="{
                    fp: null,
                    initFlatpickr() {
                        if (this.fp) this.fp.destroy();
                        this.fp = flatpickr(this.$refs.tanggalInput2, {
                            disableMobile: true,
                            enableTime: false,
                            altInput: true,
                            altFormat: 'd-M-Y',
                            dateFormat: 'd-m-Y',
                            mode: 'range',
                            onChange: (dates, str) => $wire.set('range_date', str),
                            locale: { rangeSeparator: ' Ke ' },
                        });
                    },
                    clearDate() {
                        if (this.fp) this.fp.clear(); // 🔥 kosongkan input di flatpickr
                        $wire.set('range_date', null); // 🔥 kosongkan properti Livewire
                    }
                }" x-init="initFlatpickr();
                Livewire.hook('message.processed', () => initFlatpickr());" x-ref="wrapper">

                    <input name="range_date" type="text" x-ref="tanggalInput2" wire:model.live="range_date"
                        placeholder="Pilih Tanggal"
                        class="w-full input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs join-item"
                        readonly />

                    <label @click="clearDate(); $wire.call('clearFilter')" class="btn btn-xs btn-neutral join-item"
                        title="Bersihkan Filter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-refresh-cw-icon lucide-refresh-cw">
                            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
                            <path d="M21 3v5h-5" />
                            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" />
                            <path d="M8 16H3v5" />
                        </svg>
                    </label>
                </div>
            </fieldset>
        </div>
    </div>

    <x-tabs-dashboard.layout>
        {{-- Statistik Ringkas --}}
        <div class="w-full shadow stats stats-vertical lg:stats-horizontal">

            {{-- Total Laporan --}}
            <div class="stat">
                <div class="stat-figure text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-chart-bar-big-icon lucide-chart-bar-big">
                        <path d="M3 3v16a2 2 0 0 0 2 2h16" />
                        <rect x="7" y="13" width="9" height="4" rx="1" />
                        <rect x="7" y="5" width="12" height="4" rx="1" />
                    </svg>
                </div>
                <div class="stat-title">{{ __('Total Laporan') }}</div>
                <div class="stat-value text-primary">{{ $totalHazard }}</div>
                <div class="stat-desc">{{ __('Semua laporan hazard') }}</div>
            </div>

            {{-- Sedang Diproses --}}
            <div class="stat">
                <div class="stat-figure text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-wrench-icon lucide-wrench">
                        <path
                            d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.106-3.105c.32-.322.863-.22.983.218a6 6 0 0 1-8.259 7.057l-7.91 7.91a1 1 0 0 1-2.999-3l7.91-7.91a6 6 0 0 1 7.057-8.259c.438.12.54.662.219.984z" />
                    </svg>
                </div>
                <div class="stat-title">{{ __('Sedang Diproses') }}</div>
                <div class="stat-value text-secondary">
                    {{ $hazardByStatus['in_progress'] ?? 0 }}
                </div>
                <div class="stat-desc">{{ __('Laporan aktif') }}</div>
            </div>

            {{-- Submitted --}}
            <div class="stat">
                <div class="stat-figure text-info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-hourglass-icon lucide-hourglass">
                        <path d="M5 22h14" />
                        <path d="M5 2h14" />
                        <path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22" />
                        <path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2" />
                    </svg>
                </div>
                <div class="stat-title">{{ __('Submitted') }}</div>
                <div class="stat-value text-info">
                    {{ $hazardByStatus['submitted'] ?? 0 }}
                </div>
                <div class="stat-desc">{{ __('Menunggu diproses') }}</div>
            </div>

            {{-- Closed --}}
            <div class="stat">
                <div class="stat-figure text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-book-check-icon lucide-book-check">
                        <path
                            d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20" />
                        <path d="m9 9.5 2 2 4-4" />
                    </svg>
                </div>
                <div class="stat-title">{{ __('Selesai') }}</div>
                <div class="stat-value text-success">
                    {{ $hazardByStatus['closed'] ?? 0 }}
                </div>
                <div class="stat-desc">{{ __('Laporan selesai') }}</div>
            </div>

        </div>
        {{-- Grafik --}}
        <div class="grid grid-cols-1 gap-2 my-2 lg:grid-cols-3">
            <div class="shadow rounded-xl lg:col-span-2">
                <livewire:dashboard.hazard.hazard-trand-chart />
            </div>
            <div class="shadow rounded-xl">
                <livewire:dashboard.hazard.hazard-distribusi-status />
            </div>
        </div>
        <livewire:dashboard.hazard.jenis-bahaya />
        <livewire:dashboard.hazard.env-hazard />
        <div class="grid grid-cols-1 gap-2 my-2 lg:grid-cols-2">
            <div class="shadow rounded-xl">
                <livewire:dashboard.hazard.hazard-distribusi-divisi />
            </div>
            <div class="shadow rounded-xl">
                <livewire:dashboard.hazard.hazard-user-report />
            </div>
        </div>
        <div class="shadow rounded-xl">
            <livewire:dashboard.hazard.status-by-cont-dept />
        </div>

        {{-- Daftar Laporan Terbaru --}}
        <div class="p-4 shadow rounded-xl">
            <h3 class="mb-4 font-semibold">{{ __('Laporan Hazard Terbaru') }}</h3>
            <div class="overflow-x-auto ">
                <table class="table table-xs">
                    <thead class="bg-neutral text-neutral-content">
                        <tr>
                            <th class="px-3 py-2 border">{{ __('Referensi') }}</th>
                            <th class="px-3 py-2 border">{{ __('Deskripsi') }}</th>
                            <th class="px-3 py-2 border">{{ __('Status') }}</th>
                            <th class="px-3 py-2 border">{{ __('Pelapor') }}</th>
                            <th class="px-3 py-2 border">{{ __('Tanggal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach ($latestHazardReports as $report)
                        <tr>
                            <td class="px-3 py-2 border">
                                @can('view', $report)
                                <a href="{{ route('hazard-detail', $report) }}"
                                    class="text-xs text-blue-600 hover:underline">{{ $report->no_referensi ?? '-' }}</a>
                                @else
                                <span
                                    class="text-xs text-gray-400 cursor-not-allowed">{{ $report->no_referensi ?? '-' }}</span>
                                @endcan
                            </td>
                            <td class="px-3 py-2 border">
                                {{-- Bersihkan Tag HTML dari Deskripsi --}}
                                @php
                                $cleanDescription = strip_tags($report->description);
                                $truncatedDescription = Str::limit($cleanDescription, 50, '...');
                                @endphp

                                {{-- Container Alpine.js untuk Tooltip --}}
                                <div x-data="{ showTooltip: false }" class="relative inline-block">

                                    {{-- Teks yang disingkat (sudah bersih dari HTML) --}}
                                    <span @mouseenter="showTooltip = true" @mouseleave="showTooltip = false"
                                        class="text-blue-600 cursor-pointer hover:text-blue-800">
                                        {{ $truncatedDescription }}
                                    </span>

                                    {{-- Tooltip Modal/Kotak Lengkap --}}
                                    <div x-cloak x-show="showTooltip"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-90"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-90"
                                        class="absolute z-50 w-64 p-3 mt-2 text-sm text-gray-700 whitespace-normal bg-white border border-gray-300 rounded-lg shadow-lg pointer-events-none top-full">

                                        <strong>Deskripsi Lengkap:</strong>
                                        {{-- Teks lengkap (sudah bersih dari HTML) --}}
                                        <p class="mt-1">{{ $cleanDescription }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2 border">
                                <span
                                    class="text-xs uppercase px-2  rounded
                                @if ($report->status == 'submitted') bg-yellow-100 text-yellow-800
                                @elseif($report->status == 'in_progress') bg-blue-100 text-blue-800
                                @elseif($report->status == 'pending') bg-orange-100 text-orange-800
                                @elseif($report->status == 'closed') bg-green-100 text-green-800 @endif">
                                    {{ str_replace('_', ' ', $report->status) }}
                                </span>
                            </td>
                            <td class="border">{{ $report->pelapor->name ?? $report->manualPelaporName }}</td>
                            <td class="px-3 py-2 border">{{ $report->created_at->format('d M Y') }}</td>
                        </tr>
                        @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </x-tabs-dashboard.layout>
</section>