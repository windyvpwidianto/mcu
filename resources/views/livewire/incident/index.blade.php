<section class="w-full">
    <div class="flex items-center justify-start pb-2 border-b">
        {{-- Kontrol di sisi kanan tanpa judul --}}
        <div class="flex gap-2">
            <x-button.btn-tooltip color="primary" icon="add" href="{{ route('incident-create') }}" tooltip="Tambah Data" position="top md:right" />
        </div>
    </div>
    <x-incident.layout>

        <div class="overflow-x-auto border shadow-sm rounded-xl border-base-300 bg-base-100">
            <table class="table w-full table-xs">
                <thead>
                    <tr class="border-b bg-base-200 text-base-content border-base-300">
                        <th class="w-10 text-center">No</th>

                        {{-- Nomor Referensi --}}
                        <th class="whitespace-nowrap min-w-[150px]">
                            <div class="flex items-center gap-1">
                                {{ __('Nomor Referensi') }}
                                <button class="btn btn-ghost btn-xs btn-circle" popovertarget="popover-1" style="anchor-name:--anchor-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ !empty($search) ? 'text-blue-600' : '' }}">
                                        <circle cx="11" cy="11" r="8" />
                                        <path d="m21 21-4.3-4.3" />
                                    </svg>
                                </button>
                            </div>
                            {{-- Popover Input Search --}}
                            <div class="p-3 border shadow-lg w-60 rounded-box bg-base-100 border-base-300" popover id="popover-1" style="position-anchor: --anchor-1; position-area: bottom; margin-top: 5px;">
                                <x-form.input-text type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nomor..." class="w-full input-sm" />
                            </div>
                        </th>

                        <th class="whitespace-nowrap">{{ __('Tanggal & Waktu') }}</th>

                        {{-- Kolom Judul dibuat lebih lebar --}}
                        <th>{{ __('Judul Insiden') }}</th>

                        <th class="whitespace-nowrap min-w-[180px]">
                            <div class="flex items-center gap-1">
                                {{ __('Divisi') }}
                                <button class="btn btn-ghost btn-xs btn-circle" popovertarget="pop_dept" style="anchor-name:--pop_dept">
                                    <x-icon.icon-filter :active="!empty($filterDept)" />
                                </button>
                            </div>
                            {{-- Popover Filter Dept --}}
                            <ul class="p-3 text-sm border shadow-lg rounded-box bg-base-100 border-base-300 text-base-content" popover id="pop_dept" style="position-anchor: --pop_dept; position-area: bottom; margin-top: 5px;">
                                @foreach ($filterOptions['allDivisions'] as $option)
                                <li>
                                    <label class="flex items-center p-2 rounded-md cursor-pointer hover:bg-base-200">
                                        <input type="checkbox" wire:model.live="filterDept" value="{{ $option['type'] }}-{{ $option['id'] }}" class="checkbox checkbox-xs checkbox-primary">
                                        <div class="flex flex-col ml-3">
                                            <span class="text-[9px] uppercase font-bold opacity-50">{{ $option['type'] === 'dept' ? 'Internal' : 'Contractor' }}</span>
                                            <span class="text-xs">{{ $option['name'] }}</span>
                                        </div>
                                    </label>
                                </li>
                                @endforeach
                            </ul>
                        </th>

                        <th class="whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                {{ __('Kategori Insiden') }}
                                <button class="btn btn-ghost btn-xs btn-circle" popovertarget="pop_event_combined" style="anchor-name:--pop_event_combined">
                                    <x-icon.icon-filter :active="!empty($filterEventType) || !empty($filterEventSubType)" />
                                </button>
                            </div>

                            {{-- Popover Filter Berkelompok --}}
                            <div class="p-0 overflow-hidden border shadow-xl rounded-box bg-base-100 border-base-300"
                                popover id="pop_event_combined"
                                style="position-anchor: --pop_event_combined; position-area: bottom; margin-top: 5px;">

                                <div class="flex items-center justify-between px-3 py-2 border-b bg-base-200 border-base-300">
                                    <span class="text-xs font-bold">{{ __('Filter Tipe & Jenis') }}</span>
                                </div>

                                <ul class="p-2 overflow-y-auto max-h-80 text-base-content custom-scrollbar">
                                    @foreach ($filterOptions['eventGroups'] as $type)
                                    <li class="mb-3">
                                        {{-- Parent: Event Type --}}
                                        <label class="flex items-center px-2 py-1 mb-1 rounded-md cursor-pointer bg-base-200/50 hover:bg-base-200">
                                            <input type="checkbox" wire:model.live="filterEventType" value="{{ $type->id }}" class="checkbox checkbox-xs checkbox-primary">
                                            <span class="ml-2 text-[10px] font-black uppercase text-primary tracking-wider">{{ $type->event_type_name }}</span>
                                        </label>

                                        {{-- Children: Event Sub Type --}}
                                        <ul class="ml-6 space-y-1 border-l-2 border-base-300">
                                            @foreach ($type->eventSubTypes as $sub)
                                            <li>
                                                <label class="flex items-center p-1 pl-3 rounded-md cursor-pointer hover:bg-base-100 group">
                                                    <input type="checkbox" wire:model.live="filterEventSubType" value="{{ $sub->id }}" class="checkbox checkbox-xs">
                                                    <span class="ml-2 text-xs transition-opacity opacity-70 group-hover:opacity-100">{{ $sub->event_sub_type_name }}</span>
                                                </label>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </th>

                        <th class="text-center whitespace-nowrap">{{ __('Klasifikasi') }}</th>

                        <th class="text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1">
                                {{ __('Status') }}
                                <button class="btn btn-ghost btn-xs btn-circle" popovertarget="pop_status" style="anchor-name:--pop_status">
                                    <x-icon.icon-filter :active="!empty($filterStatus)" />
                                </button>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-base-200">
                    @forelse($incidents as $index => $item)
                    <tr class="transition-colors hover:bg-base-200/50">
                        <td class="text-center font-mono text-[10px] opacity-50">
                            {{ ($incidents->currentPage() - 1) * $incidents->perPage() + $loop->iteration }}
                        </td>

                        <td class="font-bold">
                            @can('updateInitialData', $item)
                            <button class="text-xs no-underline link link-primary" wire:click="editIncident({{ $item->id }})">
                                {{ $item->report_number }}
                            </button>
                            @else
                            {{-- Jika tidak boleh: Tampilkan teks biasa (Disabled Look) --}}
                            <span class="text-xs cursor-not-allowed text-base-content/50" title="Anda tidak memiliki akses untuk mengedit/melihat detil ini">
                                {{ $item->report_number }}
                            </span>
                            @endcan
                        </td>

                        <td class="whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-xs font-semibold">{{ $item->date_time->format('d M Y') }}</span>
                                <span class="text-[10px] opacity-50">{{ $item->date_time->format('H:i') }} WITA</span>
                            </div>
                        </td>

                        <td>
                            <div class="flex flex-col ">
                                <span class="text-xs font-bold leading-tight line-clamp-2" title="{{ $item->title }}">
                                    {{ $item->title }}
                                </span>
                                <span class="text-[10px] italic opacity-60 mt-1 flex items-start gap-1">
                                    <svg class="w-3 h-3 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $item->location?->name}} ({{ $item->location_specific }})
                                </span>
                            </div>
                        </td>

                        <td>
                            <div class="badge badge-ghost border-base-300 whitespace-nowrap text-[10px] uppercase px-2">
                                {{ $item->department?->department_name ?? $item->contractor?->contractor_name }}
                            </div>
                        </td>

                        <td>
                            <div class="flex flex-col items-start gap-1">
                                {{-- Tipe Insiden (Main Category) --}}
                                <span class="badge badge-outline badge-info text-[9px] font-bold uppercase py-2">
                                    {{ $item->EventType?->event_type_name ?? 'N/A' }}
                                </span>

                                {{-- Jenis Insiden (Sub Category) --}}
                                @if($item->eventSubType)
                                <div class="flex items-center gap-1 text-[10px] text-base-content/60 italic ml-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-base-300"></span>
                                    {{ $item->eventSubType->event_sub_type_name }}
                                </div>
                                @endif
                            </div>
                        </td>

                        <td class="text-center">
                            @php
                            $riskColor = match($item->risk?->rating_name) {
                            'Ekstrem' => 'bg-error',
                            'Tinggi' => 'bg-secondary',
                            'Sedang' => 'bg-warning',
                            default => 'bg-success',
                            };
                            @endphp
                            <div class="tooltip" data-tip="{{ $item->risk?->rating_name }}">
                                <span class="inline-block w-3 h-3 rounded-full {{ $riskColor }} shadow-inner"></span>
                            </div>
                        </td>

                        <td class="text-center">
                            <div @class([ 'badge badge-sm font-bold text-[9px] uppercase w-20 py-3' , 'badge-success text-success-content'=> $item->status === 'Closed',
                                'badge-error text-error-content' => $item->status === 'Open',
                                'badge-secondary text-secondary-content' => $item->status === 'Action Required',
                                'badge-warning text-warning-content' => $item->status === 'In Progress',
                                'badge-info text-info-content' => $item->status === 'Waiting Review',
                                ])>
                                {{ $item->status }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $incidents->links() }}
        </div>
    </x-incident.layout>
</section>