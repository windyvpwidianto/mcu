<section class="w-full max-w-7xl mx-auto px-4 py-6">

    @if (session()->has('message'))
    <div class="alert alert-success mb-4 shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('message') }}</span>
        </div>
    </div>
    @endif

    {{-- TAB NAVIGASI WORKFLOW REVIEW DOKTER --}}
    <div class="tabs tabs-boxed mb-6 p-1.5 bg-base-200 rounded-xl flex flex-wrap gap-1">
        <button type="button" wire:click="setTab('pending_doctor')" class="tab py-2.5 px-4 rounded-lg transition-all {{ $activeTab === 'pending_doctor' ? 'tab-active font-bold shadow-sm' : 'hover:bg-base-300' }}">
            📋 Menunggu Review Awal 
            <span class="badge badge-sm ml-2 {{ $activeTab === 'pending_doctor' ? 'badge-primary' : 'badge-ghost' }}">{{ $pendingReviews->count() }}</span>
        </button>
        <button type="button" wire:click="setTab('need_specialist')" class="tab py-2.5 px-4 rounded-lg transition-all {{ $activeTab === 'need_specialist' ? 'tab-active font-bold shadow-sm' : 'hover:bg-base-300' }}">
            ⏳ Dalam Rujukan Spesialis 
            <span class="badge badge-sm ml-2 {{ $activeTab === 'need_specialist' ? 'badge-warning' : 'badge-ghost' }}">{{ $specialistReferrals->count() }}</span>
        </button>
        <button type="button" wire:click="setTab('pending_specialist_review')" class="tab py-2.5 px-4 rounded-lg transition-all {{ $activeTab === 'pending_specialist_review' ? 'tab-active font-bold shadow-sm' : 'hover:bg-base-300' }}">
            🩺 Siap Re-evaluasi Dokter 
            <span class="badge badge-sm ml-2 {{ $activeTab === 'pending_specialist_review' ? 'badge-success' : 'badge-ghost' }}">{{ $readyReReviews->count() }}</span>
        </button>
    </div>

    {{-- TAB 1: MENUNGGU REVIEW AWAL --}}
    @if($activeTab === 'pending_doctor')
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="card-title text-lg">Daftar Hasil MCU Menunggu Review Dokter</h2>
                <span class="text-xs text-base-content/60">Total: {{ $pendingReviews->count() }} berkas</span>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-base-200/50">
                            <th>Nama Karyawan</th>
                            <th>Kategori MCU</th>
                            <th>Tanggal MCU</th>
                            <th>Dokumen MCU</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingReviews as $result)
                        <tr class="hover:bg-base-50">
                            <td>
                                <div class="font-bold text-base-content">{{ $result->record->employee->name ?? '-' }}</div>
                                <div class="text-xs text-base-content/60">Badge: {{ $result->record->employee->employee_id ?? '-' }}</div>
                            </td>
                            <td>
                                @if($result->mcu_category)
                                    <span class="badge badge-outline badge-sm">{{ $result->mcu_category }}</span>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <td>{{ $result->record->mcu_date ? \Carbon\Carbon::parse($result->record->mcu_date)->translatedFormat('d F Y') : '-' }}</td>
                            <td>
                                @if($result->result_document)
                                <a href="{{ route('mcu.document.secure-view', \Illuminate\Support\Facades\Crypt::encryptString($result->result_document)) }}" target="_blank" class="btn btn-xs btn-outline btn-info">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Lihat Dokumen
                                </a>
                                @else
                                <span class="text-xs text-base-content/40">Tidak ada file</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <button wire:click="openReviewModal({{ $result->id }})" class="btn btn-sm btn-primary">
                                    Review Sekarang
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 italic text-base-content/50">
                                Tidak ada data hasil MCU yang menunggu review dokter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 2: DALAM RUJUKAN SPESIALIS (TEMPORARY UNFIT) --}}
    @if($activeTab === 'need_specialist')
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="card-title text-lg text-amber-700">Karyawan Dalam Masa Rujukan Dokter Spesialis (Temporary Unfit)</h2>
                    <p class="text-xs text-base-content/60">Karyawan berikut sedang menjalani konsultasi spesialis. Unggah hasil resume medis spesialis jika karyawan sudah membawa surat keterangan.</p>
                </div>
                <span class="badge badge-warning">{{ $specialistReferrals->count() }} Orang</span>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-base-200/50">
                            <th>Nama Karyawan</th>
                            <th>Rujukan Spesialis</th>
                            <th>Target Pemeriksaan</th>
                            <th>Catatan Dokter Pengirim</th>
                            <th>Dokumen Awal</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($specialistReferrals as $result)
                        @php
                            $targetDate = $result->follow_up_date ? \Carbon\Carbon::parse($result->follow_up_date) : null;
                            $isOverdue = $targetDate && $targetDate->isPast();
                        @endphp
                        <tr class="hover:bg-base-50">
                            <td>
                                <div class="font-bold text-base-content">{{ $result->record->employee->name ?? '-' }}</div>
                                <div class="text-xs text-base-content/60">Badge: {{ $result->record->employee->employee_id ?? '-' }}</div>
                                <span class="badge badge-error badge-xs mt-1">Temporary Unfit</span>
                            </td>
                            <td>
                                <span class="font-semibold text-primary">{{ $result->specialist_type ?? 'Spesialis Medis' }}</span>
                                @if($result->diseaseCategories->isNotEmpty())
                                <div class="text-[11px] text-base-content/70 mt-0.5">
                                    {{ $result->diseaseCategories->pluck('name')->join(', ') }}
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($targetDate)
                                    <div class="font-medium {{ $isOverdue ? 'text-error font-bold' : '' }}">
                                        {{ $targetDate->translatedFormat('d F Y') }}
                                    </div>
                                    <div class="text-[11px] {{ $isOverdue ? 'text-error font-semibold' : 'text-base-content/50' }}">
                                        {{ $targetDate->diffForHumans() }}
                                    </div>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <td class="max-w-xs">
                                <div class="text-xs text-base-content/80 line-clamp-2">
                                    {!! strip_tags($result->doctor_notes) !!}
                                </div>
                                <div class="text-[10px] text-base-content/50 mt-0.5">
                                    Oleh: {{ $result->reviewedBy->name ?? 'Dokter' }}
                                </div>
                            </td>
                            <td>
                                @if($result->result_document)
                                <a href="{{ route('mcu.document.secure-view', \Illuminate\Support\Facades\Crypt::encryptString($result->result_document)) }}" target="_blank" class="btn btn-xs btn-ghost btn-outline">
                                    MCU Awal
                                </a>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('mcu.referral-letter', $result->id) }}" target="_blank" class="btn btn-sm btn-outline btn-error gap-1 tooltip" data-tip="Cetak Formulir Rujukan TT-OHS-FRO-028D">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        Cetak Rujukan
                                    </a>
                                    <button wire:click="openSpecialistModal({{ $result->id }})" class="btn btn-sm btn-warning gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        Upload Hasil
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 italic text-base-content/50">
                                Tidak ada karyawan yang sedang dalam masa rujukan spesialis.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 3: SIAP RE-EVALUASI DOKTER (DOKUMEN SPESIALIS TERSEDIA) --}}
    @if($activeTab === 'pending_specialist_review')
    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="card-title text-lg text-emerald-700">Daftar Siap Re-evaluasi Dokter (Hasil Spesialis Tersedia)</h2>
                    <p class="text-xs text-base-content/60">Dokumen rujukan dari dokter spesialis telah diunggah. Dokter dapat melakukan evaluasi akhir untuk menetapkan status FIT TO WORK.</p>
                </div>
                <span class="badge badge-success">{{ $readyReReviews->count() }} Berkas</span>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="bg-base-200/50">
                            <th>Nama Karyawan</th>
                            <th>Spesialis Rujukan</th>
                            <th>Tanggal Konsultasi</th>
                            <th>Dokumen Hasil Spesialis</th>
                            <th>Ringkasan Hasil Spesialis</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($readyReReviews as $result)
                        <tr class="hover:bg-base-50">
                            <td>
                                <div class="font-bold text-base-content">{{ $result->record->employee->name ?? '-' }}</div>
                                <div class="text-xs text-base-content/60">Badge: {{ $result->record->employee->employee_id ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="font-semibold text-primary">{{ $result->specialist_type ?? '-' }}</span>
                            </td>
                            <td>
                                {{ $result->specialist_consult_date ? \Carbon\Carbon::parse($result->specialist_consult_date)->translatedFormat('d F Y') : '-' }}
                            </td>
                            <td>
                                @if($result->specialist_document)
                                <a href="{{ route('mcu.document.secure-view', \Illuminate\Support\Facades\Crypt::encryptString($result->specialist_document)) }}" target="_blank" class="btn btn-xs btn-outline btn-success gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Surat Spesialis
                                </a>
                                @else
                                <span class="text-xs text-base-content/40">-</span>
                                @endif
                            </td>
                            <td class="max-w-xs">
                                <div class="text-xs text-base-content/80 line-clamp-2">
                                    {{ $result->specialist_notes ?? 'Tidak ada catatan tertulis' }}
                                </div>
                            </td>
                            <td class="text-right">
                                <button wire:click="openReReviewModal({{ $result->id }})" class="btn btn-sm btn-success gap-1 text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Re-evaluasi Dokter
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 italic text-base-content/50">
                                Tidak ada data yang menunggu re-evaluasi dokter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ======================================================== --}}
    {{-- MODAL 1: REVIEW AWAL DOKTER                              --}}
    {{-- ======================================================== --}}
    <flux:modal wire:model="showReviewModal" flyout variant="floating" class="md:w-xl">
        <flux:heading size="lg" class="border-b pb-2 flex items-center justify-between">
            <span>Review Status MCU Awal</span>
            @php
                $currentResult = \App\Models\McuResult::find($selectedResultId);
            @endphp
            @if($currentResult && $currentResult->mcu_category)
                <span class="badge badge-primary text-xs">{{ $currentResult->mcu_category }}</span>
            @endif
        </flux:heading>

        @if($currentResult)
        <div class="bg-base-200/60 p-3 rounded-lg text-xs space-y-1 mt-3">
            <div><span class="font-bold">Karyawan:</span> {{ $currentResult->record->employee->name ?? '-' }} (ID: {{ $currentResult->record->employee->employee_id ?? '-' }})</div>
            <div><span class="font-bold">Tanggal MCU:</span> {{ $currentResult->record->mcu_date ? \Carbon\Carbon::parse($currentResult->record->mcu_date)->translatedFormat('d F Y') : '-' }}</div>
        </div>
        @endif

        <form wire:submit="saveReview" class="py-4 space-y-4">
            @if ($errors->any())
            <div class="p-3 text-sm text-red-600 bg-red-50 rounded-lg border border-red-200">
                <p class="font-bold mb-1">Gagal menyimpan karena:</p>
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div>
                <label class="label text-xs font-semibold">Pilih Status Kebugaran Medis</label>
                <select wire:model.live="fit_status" class="select select-bordered select-sm w-full">
                    <option value="">-- Pilih Status Kebugaran --</option>
                    <option value="fit_to_work">✅ Fit To Work</option>
                    <option value="fit_with_notes">⚠️ Fit With Notes (Pembatasan Kerja)</option>
                    <option value="temporary_unfit">⏳ Temporary Unfit (Perlu Rujukan Dokter Spesialis)</option>
                    <option value="unfit">❌ Unfit (Tidak Layak Bekerja)</option>
                </select>
                <x-label-error :messages="$errors->get('fit_status')" />
            </div>

            {{-- KATEGORI PENYAKIT TEMUAN (UNTUK FIT WITH NOTES & TEMPORARY UNFIT) --}}
            @if(in_array($fit_status, ['fit_with_notes', 'temporary_unfit']))
            <fieldset class="fieldset border border-base-300 p-3 rounded-lg bg-base-50/50">
                <x-form.label label="Kategori Penyakit Temuan (Bisa pilih > 1)" />

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-2 border border-base-200 rounded bg-base-100 items-center">
                    @foreach($diseaseCategories as $category)
                    <label class="cursor-pointer label justify-start space-x-2 py-1">
                        <input type="checkbox"
                            value="{{ $category->id }}"
                            wire:model="selectedDiseaseCategories"
                            class="checkbox checkbox-xs checkbox-primary">
                        <span class="label-text text-xs font-medium">{{ $category->name }}</span>
                    </label>
                    @endforeach

                    <label class="cursor-pointer label justify-start space-x-2 py-1">
                        <input type="checkbox"
                            value="tambah_penyakit"
                            wire:model.live="selectedDiseaseCategories"
                            class="checkbox checkbox-xs checkbox-secondary">
                        <span class="label-text text-xs font-semibold italic text-secondary">+ Penyakit Lainnya</span>
                    </label>

                    @if (in_array('tambah_penyakit', $selectedDiseaseCategories))
                    <div class="py-1 pr-1 col-span-2 sm:col-span-1" wire:key="box-input-penyakit-baru">
                        <div class="join w-full">
                            <input type="text"
                                wire:model="new_disease_name"
                                wire:keydown.enter.prevent="saveNewDisease"
                                placeholder="+ Ketik & Enter..."
                                class="input input-bordered input-xs join-item w-full focus:outline-none focus:border-primary text-xs" />
                            <button type="button"
                                wire:click="saveNewDisease"
                                class="btn btn-xs btn-primary join-item"
                                title="Tambah Penyakit">
                                <span wire:loading.remove.class="hidden" wire:target="saveNewDisease" class="loading loading-spinner hidden loading-xs"></span>
                                <span wire:loading.remove wire:target="saveNewDisease">+</span>
                            </button>
                        </div>
                        @error('new_disease_name')
                        <span class="text-error text-[10px] leading-tight block mt-0.5">{{ $message }}</span>
                        @enderror
                    </div>
                    @endif
                </div>
                <x-label-error :messages="$errors->get('selectedDiseaseCategories')" />
            </fieldset>
            @endif

            {{-- FIELD KHUSUS TEMPORARY UNFIT --}}
            @if($fit_status === 'temporary_unfit')
            <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-lg space-y-3">
                <div class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Informasi Rujukan Dokter Spesialis
                </div>

                <div>
                    <label class="label text-xs font-semibold py-1">Dokter Spesialis Rujukan yang Dituju <span class="text-error">*</span></label>
                    <input type="text" wire:model="specialist_type" placeholder="Contoh: Dokter Spesialis Jantung (Sp.JP) / Penyakit Dalam (Sp.PD)" class="input input-bordered input-sm w-full" />
                    <x-label-error :messages="$errors->get('specialist_type')" />
                </div>

                <div>
                    <label class="label text-xs font-semibold py-1">Batas Waktu / Target Tanggal Konsultasi <span class="text-error">*</span></label>
                    <input type="date" wire:model="follow_up_date" class="input input-bordered input-sm w-full" />
                    <x-label-error :messages="$errors->get('follow_up_date')" />
                </div>
            </div>
            @endif

            {{-- CATATAN TAMBAHAN DOKTER --}}
            <fieldset class="fieldset">
                <x-form.label label="Catatan Tambahan Dokter" :required="$fit_status !== 'fit_to_work'" />
                <div x-data="ckeditorHelper('doctor_notes')" wire:ignore>
                    <div x-ref="editorElement" data-placeholder="{{ __('Masukkan Catatan Tambahan Dokter...') }}"></div>
                </div>
                <x-label-error :messages="$errors->get('doctor_notes')" />
            </fieldset>

            <div class="flex justify-end space-x-2 pt-4 border-t border-base-200">
                <flux:button size="xs" variant="ghost" wire:click="$set('showReviewModal', false)">Batal</flux:button>
                <flux:button type="submit" size="xs" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove.class="hidden" wire:target="saveReview" class="loading loading-spinner hidden loading-sm mr-1"></span>
                    Simpan Review
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- ======================================================== --}}
    {{-- MODAL 2: UPLOAD DOKUMEN HASIL DOKTER SPESIALIS          --}}
    {{-- ======================================================== --}}
    <flux:modal wire:model="showSpecialistModal" flyout variant="floating" class="md:w-lg">
        <flux:heading size="lg" class="border-b pb-2">
            Upload Hasil Dokter Spesialis
        </flux:heading>

        @php
            $targetSpecialistResult = \App\Models\McuResult::find($specialistResultId);
        @endphp
        @if($targetSpecialistResult)
        <div class="bg-base-200/60 p-3 rounded-lg text-xs space-y-1 mt-3">
            <div><span class="font-bold">Karyawan:</span> {{ $targetSpecialistResult->record->employee->name ?? '-' }}</div>
            <div><span class="font-bold">Rujukan:</span> <span class="badge badge-sm badge-outline badge-primary">{{ $targetSpecialistResult->specialist_type ?? 'Spesialis' }}</span></div>
            <div><span class="font-bold">Target Deadline:</span> {{ $targetSpecialistResult->follow_up_date ? \Carbon\Carbon::parse($targetSpecialistResult->follow_up_date)->translatedFormat('d F Y') : '-' }}</div>
        </div>
        @endif

        <form wire:submit="saveSpecialistDocument" class="py-4 space-y-4">
            @if ($errors->any())
            <div class="p-3 text-sm text-red-600 bg-red-50 rounded-lg border border-red-200">
                <p class="font-bold mb-1">Gagal menyimpan karena:</p>
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div>
                <label class="label text-xs font-semibold py-1">Unggah Surat Keterangan / Resume Dokter Spesialis <span class="text-error">*</span></label>
                <input type="file" wire:model="upload_specialist_document" class="file-input file-input-bordered file-input-sm w-full" accept=".pdf,.jpg,.jpeg,.png" />
                <div class="text-[11px] text-base-content/50 mt-1">Maks. 10MB (PDF, JPG, PNG)</div>
                <div wire:loading wire:target="upload_specialist_document" class="text-xs text-primary font-medium mt-1">Mengunggah file...</div>
                <x-label-error :messages="$errors->get('upload_specialist_document')" />
            </div>

            <div>
                <label class="label text-xs font-semibold py-1">Tanggal Konsultasi / Pemeriksaan Spesialis <span class="text-error">*</span></label>
                <input type="date" wire:model="upload_specialist_consult_date" class="input input-bordered input-sm w-full" />
                <x-label-error :messages="$errors->get('upload_specialist_consult_date')" />
            </div>

            <div>
                <label class="label text-xs font-semibold py-1">Catatan / Ringkasan Hasil Pemeriksaan Spesialis</label>
                <textarea wire:model="upload_specialist_notes" rows="3" placeholder="Masukkan ringkasan kesimpulan atau anjuran dari dokter spesialis..." class="textarea textarea-bordered textarea-sm w-full"></textarea>
                <x-label-error :messages="$errors->get('upload_specialist_notes')" />
            </div>

            <div class="flex justify-end space-x-2 pt-4 border-t border-base-200">
                <flux:button size="xs" variant="ghost" wire:click="$set('showSpecialistModal', false)">Batal</flux:button>
                <flux:button type="submit" size="xs" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove.class="hidden" wire:target="saveSpecialistDocument" class="loading loading-spinner hidden loading-sm mr-1"></span>
                    Simpan & Ajukan ke Dokter
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- ======================================================== --}}
    {{-- MODAL 3: RE-EVALUASI DOKTER (SECOND REVIEW)              --}}
    {{-- ======================================================== --}}
    <flux:modal wire:model="showReReviewModal" flyout variant="floating" class="md:w-xl">
        <flux:heading size="lg" class="border-b pb-2 flex items-center justify-between">
            <span>Re-evaluasi Dokter Onsite</span>
            <span class="badge badge-success text-xs">Evaluasi Hasil Spesialis</span>
        </flux:heading>

        @php
            $reReviewTarget = \App\Models\McuResult::with(['record.employee', 'reviewedBy'])->find($reReviewResultId);
        @endphp
        @if($reReviewTarget)
        <div class="bg-base-200/60 p-3 rounded-lg text-xs space-y-2 mt-3">
            <div><span class="font-bold">Karyawan:</span> {{ $reReviewTarget->record->employee->name ?? '-' }} (ID: {{ $reReviewTarget->record->employee->employee_id ?? '-' }})</div>
            <div><span class="font-bold">Spesialis Rujukan:</span> <span class="badge badge-sm badge-outline badge-primary">{{ $reReviewTarget->specialist_type ?? '-' }}</span></div>
            <div><span class="font-bold">Tanggal Konsultasi:</span> {{ $reReviewTarget->specialist_consult_date ? \Carbon\Carbon::parse($reReviewTarget->specialist_consult_date)->translatedFormat('d F Y') : '-' }}</div>
            
            <div class="pt-1 flex flex-wrap gap-2">
                @if($reReviewTarget->specialist_document)
                <a href="{{ route('mcu.document.secure-view', \Illuminate\Support\Facades\Crypt::encryptString($reReviewTarget->specialist_document)) }}" target="_blank" class="btn btn-xs btn-outline btn-success gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    Buka Surat & Resume Dokter Spesialis
                </a>
                @endif
                <a href="{{ route('mcu.referral-letter', $reReviewTarget->id) }}" target="_blank" class="btn btn-xs btn-outline btn-info gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Surat Rujukan Awal (TT-OHS-FRO-028D)
                </a>
            </div>

            @if($reReviewTarget->specialist_notes)
            <div class="bg-base-100 p-2 rounded border border-base-300 mt-2">
                <span class="font-bold block text-base-content/70">Catatan dari Spesialis:</span>
                <p class="text-xs text-base-content mt-0.5">{{ $reReviewTarget->specialist_notes }}</p>
            </div>
            @endif
        </div>
        @endif

        <form wire:submit="saveReReview" class="py-4 space-y-4">
            @if ($errors->any())
            <div class="p-3 text-sm text-red-600 bg-red-50 rounded-lg border border-red-200">
                <p class="font-bold mb-1">Gagal menyimpan karena:</p>
                <ul class="list-disc pl-4 space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div>
                <label class="label text-xs font-semibold">Keputusan Akhir Status Kebugaran Medis <span class="text-error">*</span></label>
                <select wire:model.live="re_fit_status" class="select select-bordered select-sm w-full font-medium">
                    <option value="fit_to_work">✅ Fit To Work (Layak Bekerja)</option>
                    <option value="fit_with_notes">⚠️ Fit With Notes (Layak dengan Catatan/Batasan)</option>
                    <option value="temporary_unfit">⏳ Temporary Unfit (Masih Perlu Tindak Lanjut)</option>
                    <option value="unfit">❌ Unfit (Tidak Layak Bekerja)</option>
                </select>
                <x-label-error :messages="$errors->get('re_fit_status')" />
            </div>

            <div>
                <label class="label text-xs font-semibold">Catatan Evaluasi Akhir Dokter <span class="text-error">*</span></label>
                <textarea wire:model="re_review_notes" rows="4" placeholder="Contoh: Kondisi kesehatan telah dievaluasi oleh Sp.JP, hasil pemeriksaan dalam batas aman dan terkontrol. Karyawan dinyatakan FIT TO WORK..." class="textarea textarea-bordered textarea-sm w-full"></textarea>
                <x-label-error :messages="$errors->get('re_review_notes')" />
            </div>

            <div class="flex justify-end space-x-2 pt-4 border-t border-base-200">
                <flux:button size="xs" variant="ghost" wire:click="$set('showReReviewModal', false)">Batal</flux:button>
                <flux:button type="submit" size="xs" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove.class="hidden" wire:target="saveReReview" class="loading loading-spinner hidden loading-sm mr-1"></span>
                    Simpan Re-evaluasi & Terbitkan Sertifikat
                </flux:button>
            </div>
        </form>
    </flux:modal>

</section>
