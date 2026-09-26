<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Daftar Hasil MCU (Pending & Reviewed)</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-gray-100 uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3">Nama Karyawan</th>
                    <th class="px-4 py-3">Jadwal MCU</th>
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
                    <td class="px-4 py-3 flex gap-2 items-center">
                        <button wire:click="openReviewModal({{ $result->id }})" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                            Lihat Detail
                        </button>

                        @if($result->workflow_status === 'reviewed' && in_array($result->status, ['fit_to_work', 'fit_with_notes']))
                            <a href="{{ route('mcu.fit-letter', $result->id) }}" target="_blank" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 font-semibold flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                {{ $result->certificate_path ? 'Unduh Sertifikat (.docx)' : 'Cetak Surat FIT (PDF)' }}
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
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

            <div class="modal-action mt-6 border-t pt-4">
                <button wire:click="closeReviewModal" class="btn btn-ghost">Tutup</button>
            </div>

        </div>

        <div class="modal-backdrop" wire:click="closeReviewModal">
            <button class="cursor-default">close</button>
        </div>
    </div>
</div>