<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Daftar Hasil MCU (Pending & Reviewed)</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-gray-100 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3">Nama Karyawan</th>
                    <th class="px-4 py-3">Jadwal MCU</th>
                    <th class="px-4 py-3">Kategori MCU</th>
                    <th class="px-4 py-3">Status Kebugaran</th>
                    <th class="px-4 py-3">Status Dokumen</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($mcuResults as $result)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900">
                        {{ $result->masterData?->employee_name ?? $result->record?->employee?->name ?? 'Tidak diketahui' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $result->record?->mcu_date ? \Carbon\Carbon::parse($result->record->mcu_date)->translatedFormat('d F Y') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($result->mcu_category)
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-medium">{{ $result->mcu_category }}</span>
                        @else
                            <span class="text-gray-400 italic text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($result->status)
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs uppercase">
                            {{ str_replace('_', ' ', $result->status) }}
                        </span>
                        @else
                        <span class="text-gray-400 italic">Belum direview</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($result->workflow_status === 'reviewed')
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs uppercase">
                            Selesai Direview
                        </span>
                        @else
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs uppercase">
                            Menunggu Dokter
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="dropdown dropdown-left dropdown-bottom">
                            <label tabindex="0" class="btn btn-sm btn-outline text-gray-600 m-0">
                                Aksi
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </label>
                            <ul tabindex="0" class="dropdown-content z-[50] menu p-2 shadow-lg bg-white rounded-box w-52 border border-gray-100 text-sm">
                                <li>
                                    <button wire:click="openReviewModal({{ $result->id }})" class="text-indigo-600 hover:bg-indigo-50 hover:text-indigo-700 font-medium">
                                        Lihat Detail
                                    </button>
                                </li>

                                @if($result->result_document)
                                <li>
                                    <a href="{{ route('mcu.document.secure-view', \Illuminate\Support\Facades\Crypt::encryptString($result->result_document)) }}" target="_blank" class="text-blue-600 hover:bg-blue-50 hover:text-blue-700 font-medium">
                                        Lihat Dokumen
                                    </a>
                                </li>
                                @endif

                                @if($result->workflow_status === 'reviewed' && in_array($result->status, ['fit_to_work', 'fit_with_notes']))
                                <li class="menu-title mt-1 pb-0">
                                    <span class="text-[10px] text-gray-400 uppercase tracking-wider px-2">Surat Keterangan FIT</span>
                                </li>
                                <li>
                                    <a href="{{ route('mcu.fit-letter.edit', $result->id) }}" class="text-amber-600 hover:bg-amber-50 hover:text-amber-700 font-medium justify-between">
                                        <span>Edit Surat</span>
                                        <span class="text-[9px] px-1 py-0.5 rounded uppercase font-extrabold {{ ($result->letter_status ?? 'draft') === 'final' ? 'bg-emerald-600 text-white' : 'bg-amber-200 text-amber-900' }}">
                                            {{ $result->letter_status ?? 'draft' }}
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('mcu.fit-letter', $result->id) }}" target="_blank" class="text-green-600 hover:bg-green-50 hover:text-green-700 font-medium">
                                        Cetak PDF
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        Tidak ada data MCU dengan status tersebut.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $mcuResults->links() }}
    </div>

    <div class="modal {{ $showReviewModal ? 'modal-open' : '' }}" role="dialog">
        <div class="modal-box w-11/12 max-w-2xl bg-white">

            <button wire:click="closeReviewModal" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>

            <h3 class="font-bold text-lg border-b pb-2 mb-4">
                Review Medis Karyawan: <span class="text-primary">{{ $employeeName }}</span>
            </h3>

            <div class="space-y-4">

                <div class="form-control w-full">
                    <label class="label font-semibold">
                        <span class="label-text">Kategori MCU</span>
                    </label>
                    <div class="p-3 bg-base-50 border border-base-200 rounded-lg">
                        @php $modalResult = collect($mcuResults->items())->firstWhere('id', $selectedResultId); @endphp
                        @if($modalResult?->mcu_category)
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-medium">{{ $modalResult->mcu_category }}</span>
                        @else
                            <span class="text-gray-400 italic">-</span>
                        @endif
                    </div>
                </div>

                <div class="form-control w-full">
                    <label class="label font-semibold">
                        <span class="label-text">Status Kebugaran Kerja (Fit Status)</span>
                    </label>
                    <div class="p-3 bg-base-50 border border-base-200 rounded-lg">
                        {{ $fit_status ? str_replace('_', ' ', strtoupper($fit_status)) : '-' }}
                    </div>
                </div>

                @if($fit_status === 'fit_with_notes')
                <div class="form-control w-full">
                    <label class="label font-semibold">
                        <span class="label-text">Catatan Batasan Kerja (Site Consult)</span>
                    </label>
                    <div class="p-3 bg-base-50 border border-base-200 rounded-lg min-h-[4rem]">
                        {{ $restriction_notes ?: '-' }}
                    </div>
                </div>
                @endif

                @if($fit_status === 'temporary_unfit')
                <div class="form-control w-full">
                    <label class="label font-semibold">
                        <span class="label-text">Jadwal MCU Follow Up</span>
                    </label>
                    <div class="p-3 bg-base-50 border border-base-200 rounded-lg">
                        {{ $follow_up_date ? \Carbon\Carbon::parse($follow_up_date)->translatedFormat('d F Y') : '-' }}
                    </div>
                </div>
                @endif

                <div class="form-control w-full">
                    <label class="label font-semibold">
                        <span class="label-text">Catatan Internal Dokter</span>
                    </label>
                    <div class="p-3 bg-base-50 border border-base-200 rounded-lg min-h-[4rem] prose prose-sm max-w-none">
                        {!! $doctor_notes ?: '-' !!}
                    </div>
                </div>

            </div>

            <div class="modal-action mt-6 border-t pt-4 flex justify-between items-center w-full">
                <div class="flex items-center gap-2">
                    @if($selectedResultId && in_array($fit_status, ['fit_to_work', 'fit_with_notes']))
                        @php
                            $currentResult = collect($mcuResults->items())->firstWhere('id', $selectedResultId);
                        @endphp
                        @if($currentResult)
                            <a href="{{ route('mcu.fit-letter.edit', $currentResult->id) }}" class="btn btn-warning btn-sm text-slate-900 font-bold flex items-center gap-2 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Draft Surat
                            </a>

                            <a href="{{ route('mcu.fit-letter', $currentResult->id) }}" target="_blank" class="btn btn-success btn-sm text-white flex items-center gap-2 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Cetak PDF
                            </a>
                        @endif
                    @endif
                </div>
                <button wire:click="closeReviewModal" class="btn btn-ghost">Tutup</button>
            </div>

        </div>

        <div class="modal-backdrop" wire:click="closeReviewModal">
            <button class="cursor-default">close</button>
        </div>
    </div>
</div>