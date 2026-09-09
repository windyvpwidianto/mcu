<section class="w-full">
    <x-toast />
    @include('partials.header-hazard')
    <div class="flex flex-col items-start md:flex-row md:justify-between md:items-center">
        <div class="flex items-center gap-2 mb-4 md:mb-0">
            {{-- <div class="tooltip tooltip-right  mb-0.5" data-tip="Tambah Hazard">
                <a href="{{ route('hazard-form') }}" class="btn btn-square btn-primary btn-xs">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd"
                    d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z"
                    clip-rule="evenodd" />
            </svg>
            </a>
        </div> --}}
        <x-button.btn-tooltip color="primary" icon="add" href="{{ route('hazard-form') }}" tooltip="Tambah Data" />
        @if (
        $filterByAuth ||
        ($filterStatus && $filterStatus !== 'all') ||
        !empty($filterDepartment) ||
        !empty($filterContractor) ||
        !empty($filterEventType) ||
        !empty($filterEventSubType) ||
        !empty($filterReporterDept) ||
        $searchPelapor ||
        ($start_date && $end_date))
        <x-button.btn-tooltip color="success" icon="file-download" wireClick="export" tooltip="Download" />

        @endif
        {{-- @livewire('hazard.import-hazard-reports-modal') --}}
        {{-- Tambahkan wire:model.live untuk memfilter secara real-time --}}
        <input type="checkbox" id="myReportsCheckbox" wire:model.live="filterByAuth"
            class="checkbox checkbox-info" />
        <label for="myReportsCheckbox" class="text-sm font-medium text-gray-700 cursor-pointer">
            {{ __('Hanya Laporan Saya') }}
        </label>
    </div>
    <div class="flex flex-col w-full gap-4 md:flex-row md:max-w-md">
        <fieldset class="w-full fieldset">
            <x-form.label label="Cari Pelapor" required />
            <div class="relative">
                <!-- Input Search -->
                <input name="searchLocation" type="text" wire:model.live.debounce.300ms="searchPelapor"
                    placeholder="Cari Pelapor..."
                    class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('location_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                <!-- Dropdown hasil search -->
                @if ($showPelaporDropdown && count($pelapors) > 0)
                <ul
                    class="absolute z-10 w-full mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60">
                    <!-- Spinner ketika klik -->
                    <div wire:loading wire:target="selectPelapor" class="p-2 text-center">
                        <span class="loading loading-spinner loading-sm text-secondary"></span>
                    </div>
                    @foreach ($pelapors as $user)
                    <li wire:click="selectPelapor({{ $user->id }}, '{{ $user->name }}')"
                        class="px-3 py-2 cursor-pointer hover:bg-base-200">
                        {{ $user->name }}
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </fieldset>
        <fieldset class="w-full fieldset">
            <x-form.label label="rentang tanggal" required />
            <div class="relative" wire:ignore x-data="{
                    fp: null,
                    initFlatpickr() {
                        if (this.fp) this.fp.destroy();
                        this.fp = flatpickr(this.$refs.tanggalInput2, {
                            disableMobile: true,
                            enableTime: false,
                            altInput: true,
                            altFormat: 'd-M-Y',
                            dateFormat: 'd-m-Y',
                            mode: 'range', // 👈 Tambahkan opsi ini
                            onChange: (dates, str) => $wire.set('action_due_date', str),
                        });
                    }
                }" x-init="initFlatpickr();
                Livewire.hook('message.processed', () => initFlatpickr());" x-ref="wrapper">
                <input name="action_due_date" type="text" x-ref="tanggalInput2" wire:model.live="action_due_date"
                    placeholder="Pilih Tanggal"
                    class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('action_due_date') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}"
                    readonly />
            </div>
            <x-label-error :messages="$errors->get('action_due_date')" />
        </fieldset>
    </div>
    </div>
    {{-- <x-manhours.layout> --}}
    <div class="mt-4 overflow-x-auto max-h-[calc(100vh-18rem)] 2xl:max-h-[calc(100vh-20rem)] ">
        <table class="table text-xs border table-xs">
            <thead>
                <tr class="text-center bg-base-100">
                    <th class="border">#</th>
                    <th class="border">{{ __('reference') }}</th>
                    <th class="border">{{ __('Tipe Bahaya') }}
                        {{-- Button Trigger Popover --}}
                        <button class="btn btn-ghost btn-xs" popovertarget="eventType" style="anchor-name:--eventType">
                            <span class="text-xs {{ !empty($filterEventType) ? 'text-blue-600' : '' }}">
                                @if (empty($filterEventType))
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-list-filter">
                                    <path d="M2 5h20" />
                                    <path d="M6 12h12" />
                                    <path d="M9 19h6" />
                                </svg>
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-arrow-down-wide-narrow">
                                    <path d="m3 16 4 4 4-4" />
                                    <path d="M7 20V4" />
                                    <path d="M11 4h10" />
                                    <path d="M11 8h7" />
                                    <path d="M11 12h4" />
                                </svg>
                                @endif
                            </span>
                        </button>

                        {{-- Popover Content --}}
                        <ul class="p-2 overflow-y-auto shadow-lg dropdown menu w-52 rounded-box bg-base-100 max-h-60"
                            popover id="eventType" style="position-anchor:--eventType; inset-area: bottom span-right;">

                            @foreach ($filterOptions['EventType'] as $event_type)
                            <li>
                                <label class="flex items-center p-1 rounded cursor-pointer hover:bg-gray-100">
                                    <input type="checkbox" wire:model.live="filterEventType"
                                        value="{{ $event_type->id }}" class="text-blue-600 rounded form-checkbox">
                                    <span class="ml-2 text-xs capitalize">
                                        {{ $event_type->event_type_name }}
                                    </span>
                                </label>
                            </li>
                            @endforeach
                        </ul>
                    </th>
                    <th class="border">{{ __('Jenis Bahaya') }}
                        <button class="btn btn-ghost btn-xs" popovertarget="eventSubType"
                            style="anchor-name:--eventSubType">
                            <span class="text-xs {{ !empty($filterEventSubType) ? 'text-blue-600' : '' }}">
                                @if (empty($filterEventSubType))
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-list-filter-icon lucide-list-filter">
                                    <path d="M2 5h20" />
                                    <path d="M6 12h12" />
                                    <path d="M9 19h6" />
                                </svg>
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-arrow-down-wide-narrow-icon lucide-arrow-down-wide-narrow">
                                    <path d="m3 16 4 4 4-4" />
                                    <path d="M7 20V4" />
                                    <path d="M11 4h10" />
                                    <path d="M11 8h7" />
                                    <path d="M11 12h4" />
                                </svg>
                                @endif
                            </span>
                        </button>
                        <ul class="p-2 overflow-y-auto shadow-lg dropdown menu w-52 rounded-box bg-base-100 max-h-60"
                            popover id="eventSubType"
                            style="position-anchor:--eventSubType; inset-area: bottom span-right;">
                            {{-- Loop Department --}}
                            @foreach ($filterOptions['EventSubType'] as $event_sub_type)
                            <li>
                                <label class="flex items-center p-1 rounded cursor-pointer hover:bg-gray-100">
                                    <input type="checkbox" wire:model.live="filterEventSubType"
                                        value="{{ $event_sub_type->id }}"
                                        class="text-blue-600 rounded form-checkbox">
                                    <span
                                        class="ml-2 text-xs capitalize">{{ $event_sub_type->event_sub_type_name }}</span>
                                </label>
                            </li>
                            @endforeach
                        </ul>
                    </th>
                    <th class="border">{{ __('Divisi Penanggung Jawab') }}
                        <button class="btn btn-ghost btn-xs" popovertarget="divisi_dept"
                            style="anchor-name:--divisi_dept">
                            {{-- Ikon Filter: Tampilkan jika filterDepartment tidak kosong --}}
                            <span
                                class="text-xs {{ !empty($filterDepartment) || !empty($filterContractor) ? 'text-blue-600' : '' }}">
                                @if (empty($filterDepartment) && empty($filterContractor))
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-list-filter-icon lucide-list-filter">
                                    <path d="M2 5h20" />
                                    <path d="M6 12h12" />
                                    <path d="M9 19h6" />
                                </svg>
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-arrow-down-wide-narrow-icon lucide-arrow-down-wide-narrow">
                                    <path d="m3 16 4 4 4-4" />
                                    <path d="M7 20V4" />
                                    <path d="M11 4h10" />
                                    <path d="M11 8h7" />
                                    <path d="M11 12h4" />
                                </svg>
                                @endif
                            </span>
                        </button>
                        <ul class="p-2 overflow-y-auto shadow-lg dropdown menu w-52 rounded-box bg-base-100 max-h-60"
                            popover id="divisi_dept"
                            style="position-anchor:--divisi_dept; inset-area: bottom span-right;">
                            {{-- Loop Department --}}
                            @foreach ($filterOptions['Department'] as $dept)
                            <li>
                                <label class="flex items-center p-1 rounded cursor-pointer hover:bg-gray-100">
                                    <input type="checkbox" wire:model.live="filterDepartment"
                                        value="{{ $dept->id }}" class="text-blue-600 rounded form-checkbox">
                                    <span class="ml-2 text-xs capitalize">{{ $dept->department_name }}</span>
                                </label>
                            </li>
                            @endforeach
                            {{-- Loop Contractor --}}
                            @foreach ($filterOptions['Contractors'] as $cont)
                            <li>
                                <label class="flex items-center p-1 rounded cursor-pointer hover:bg-gray-100">
                                    <input type="checkbox" wire:model.live="filterContractor"
                                        value="{{ $cont->id }}" class="text-blue-600 rounded form-checkbox">

                                    <span class="ml-2 text-xs capitalize">{{ $cont->contractor_name }}</span>
                                </label>
                            </li>
                            @endforeach
                        </ul>
                    </th>
                    <th class="border">{{ __('Pelapor') }}</th>
                    <th class="border">{{ __('Divisi Pelapor') }}
                        <button class="btn btn-ghost btn-xs" popovertarget="popover_reporter_dept"
                            style="anchor-name:--anchor_reporter_dept">
                            <span class="text-xs {{ !empty($filterReporterDept) ? 'text-blue-600' : '' }}">
                                @if (empty($filterReporterDept))
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-list-filter">
                                    <path d="M2 5h20" />
                                    <path d="M6 12h12" />
                                    <path d="M9 19h6" />
                                </svg>
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-arrow-down-wide-narrow">
                                    <path d="m3 16 4 4 4-4" />
                                    <path d="M7 20V4" />
                                    <path d="M11 4h10" />
                                    <path d="M11 8h7" />
                                    <path d="M11 12h4" />
                                </svg>
                                @endif
                            </span>
                        </button>

                        <ul class="p-2 overflow-y-auto shadow-lg dropdown menu w-52 rounded-box bg-base-100 max-h-60"
                            popover id="popover_reporter_dept"
                            style="position-anchor:--anchor_reporter_dept; inset-area: bottom span-right;">

                            @foreach ($filterOptions['ReporterDepartments'] as $deptName)
                            <li>
                                <label class="flex items-center p-1 rounded cursor-pointer hover:bg-gray-100">
                                    <input type="checkbox" wire:model.live="filterReporterDept"
                                        value="{{ $deptName }}" class="text-blue-600 rounded form-checkbox">
                                    <span class="ml-2 text-xs capitalize">{{ $deptName }}</span>
                                </label>
                            </li>
                            @endforeach

                            @if (!empty($filterReporterDept))
                            <li class="mt-2 border-t">
                                <button wire:click="$set('filterReporterDept', [])"
                                    class="w-full text-red-500 btn btn-ghost btn-xs">Reset Filter</button>
                            </li>
                            @endif
                        </ul>
                    </th>
                    <th class="relative border">
                        {{ __('Status') }}
                        <button class="btn btn-ghost btn-xs" popovertarget="popover-1"
                            style="anchor-name:--anchor-1">

                            <span class="text-xs {{ !empty($filterStatus) ? 'text-blue-600' : '' }}">
                                @if (empty($filterStatus))
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-list-filter-icon lucide-list-filter">
                                    <path d="M2 5h20" />
                                    <path d="M6 12h12" />
                                    <path d="M9 19h6" />
                                </svg>
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-arrow-down-wide-narrow-icon lucide-arrow-down-wide-narrow">
                                    <path d="m3 16 4 4 4-4" />
                                    <path d="M7 20V4" />
                                    <path d="M11 4h10" />
                                    <path d="M11 8h7" />
                                    <path d="M11 12h4" />
                                </svg>
                                @endif
                            </span>
                        </button>
                        {{-- Dropdown Menu --}}
                        <ul class="shadow-sm dropdown menu w-52 rounded-box bg-base-100" popover id="popover-1"
                            style="position-anchor:--anchor-1">

                            {{-- Loop Isi Dropdown --}}
                            @foreach ($availableStatuses as $status)
                            <li>
                                <label class="flex items-center p-1 mb-1 rounded cursor-pointer hover:bg-gray-100">
                                    <input type="checkbox" wire:model.live="filterStatus"
                                        value="{{ $status }}" class="text-blue-600 rounded form-checkbox">
                                    <span
                                        class="ml-2 text-xs capitalize">{{ str_replace('_', ' ', $status) }}</span>
                                </label>
                            </li>
                            @endforeach
                        </ul>
                    </th>
                    <th class="border">{{ __('Deskripsi') }}</th>

                    <th class="border">{{ __('Tanggal') }}</th>
                    <th class="flex-col text-center border">
                        {{ __('Aksi') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $no => $report)
                <tr class="hover:bg-primary/40">
                    <td class="border">{{ $reports->firstItem() + $no }}</td>
                    <td class="border">
                        @can('view', $report)
                        <a href="{{ route('hazard-detail', $report) }}"
                            class="text-xs text-blue-600 hover:underline">{{ $report->no_referensi ?? '-' }}</a>
                        @else
                        <span
                            class="text-xs text-gray-400 cursor-not-allowed">{{ $report->no_referensi ?? '-' }}</span>
                        @endcan
                    </td>
                    <td class="border">{{ $report->eventType->event_type_name ?? '-' }}</td>
                    <td class="border">{{ $report->eventSubType->event_sub_type_name ?? '-' }}</td>
                    <td class="border">
                        {{ $report->department->department_name ?? $report->contractor->contractor_name }}
                    </td>
                    <td class="border">{{ $report->pelapor->name ?? $report->manualPelaporName }}</td>
                    <td class="border">
                        {{ $report->pelapor->department_name ?? '#N/A' }}
                    </td>
                    <td class="text-xs border">
                        <span
                            class="badge badge-xs  badge-outline {{ $this->getRandomBadgeColor($report->status) }} uppercase px-2">
                            {{ str_replace('_', ' ', $report->status) }}
                        </span>
                    </td>
                    <td class="border">
                        {{-- Bersihkan Tag HTML dari Deskripsi --}}
                        @php
                        $cleanDescription = strip_tags($report->description);
                        $truncatedDescription = Str::limit($cleanDescription, 50, '...');
                        @endphp

                        {{-- Container Alpine.js untuk Tooltip --}}
                        <div x-data="{ showTooltip: false }" class="relative inline-block">

                            {{-- Teks yang disingkat (sudah bersih dari HTML) --}}
                            <span @mouseenter="showTooltip = true" @mouseleave="showTooltip = false"
                                class="font-medium cursor-pointer text-base-content hover:text-primary">
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
                                class="absolute z-[9999] p-3 mt-2 text-sm text-base-content whitespace-normal bg-base-100 border border-base-300 rounded-lg shadow-lg pointer-events-none top-full w-80">

                                <strong>Deskripsi Lengkap:</strong>
                                {{-- Teks lengkap (sudah bersih dari HTML) --}}
                                <p class="mt-1">{{ $cleanDescription }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="border">{{ \Carbon\Carbon::parse($report->tanggal)->format('d M Y') }}</td>
                    <td class="p-2 border">
                        <div class="flex flex-col items-center justify-center leading-tight divide-y">
                            {{-- Baris Total --}}
                            <div class="flex items-baseline gap-1 ">
                                <span
                                    class="text-sm font-bold text-base-content">{{ $report->total_due_dates }}</span>
                                <span class="text-[10px] italic font-semibold text-gray-400">total </span>
                            </div>

                            {{-- Baris Open --}}
                            <div class="flex items-baseline gap-1">
                                <span
                                    class="text-sm font-bold text-base-content">{{ $report->pending_actual_closes }}</span>
                                <span class="text-[10px] italic font-semibold text-gray-400">Open</span>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-4 text-center text-gray-500">Tidak ada laporan ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $reports->links() }}
    {{-- </x-manhours.layout> --}}
</section>