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
                        {{ $result->participant->employee->name ?? 'Tidak diketahui' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $result->participant->schedule->schedule_date->format('d M Y') ?? '-' }}
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
                        <button wire:click="openReviewModal({{ $result->id }})" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                            Lihat Detail
                        </button>
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
                        <span class="label-text">Status Kebugaran Kerja (Fit Status) <span class="text-error">*</span></span>
                    </label>
                    <select wire:model.live="fit_status" class="select select-bordered w-full @error('fit_status') select-error @enderror">
                        <option value="">-- Pilih Status Kebugaran --</option>
                        <option value="fit_to_work">Fit To Work (Sehat)</option>
                        <option value="fit_with_notes">Fit With Notes (Fit dengan Catatan/Batasan)</option>
                        <option value="temporary_unfit">Temporary Unfit (Tidak Fit Sementara)</option>
                        <option value="unfit">Unfit (Tidak Fit Permanen)</option>
                    </select>
                    @error('fit_status') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                </div>

                @if($fit_status === 'fit_with_notes')
                <div class="form-control w-full">
                    <label class="label font-semibold">
                        <span class="label-text">Catatan Batasan Kerja (Site Consult) <span class="text-error">*</span></span>
                    </label>
                    <textarea wire:model="restriction_notes" class="textarea textarea-bordered h-24 @error('restriction_notes') textarea-error @enderror" placeholder="Contoh: Tidak boleh mengangkat beban lebih dari 10kg, tidak boleh bekerja di ketinggian..."></textarea>
                    @error('restriction_notes') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                </div>
                @endif

                @if($fit_status === 'temporary_unfit')
                <div class="form-control w-full">
                    <label class="label font-semibold">
                        <span class="label-text">Jadwal MCU Follow Up <span class="text-error">*</span></span>
                    </label>
                    <input type="date" wire:model="follow_up_date" class="input input-bordered w-full @error('follow_up_date') input-error @enderror" />
                    @error('follow_up_date') <span class="text-error text-sm mt-1">{{ $message }}</span> @enderror
                </div>
                @endif

                <div class="form-control w-full">
                    <label class="label font-semibold">
                        <span class="label-text">Catatan Internal Dokter (Opsional)</span>
                    </label>
                    <textarea wire:model="doctor_notes" class="textarea textarea-bordered h-20" placeholder="Catatan medis tambahan (hanya dilihat oleh tim medis)..."></textarea>
                </div>

            </div>

            <div class="modal-action mt-6 border-t pt-4">
                <button wire:click="closeReviewModal" class="btn btn-ghost">Batal</button>

                <button wire:click="saveReview" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading wire:target="saveReview" class="loading loading-spinner loading-sm"></span>
                    Simpan & Kirim Notifikasi
                </button>
            </div>

        </div>

        <div class="modal-backdrop" wire:click="closeReviewModal">
            <button class="cursor-default">close</button>
        </div>
    </div>
</div>