<section class="w-full max-w-7xl mx-auto px-4 py-6">

    @if (session()->has('message'))
    <div class="alert alert-success mb-4">{{ session('message') }}</div>
    @endif

    <div class="card bg-base-100 shadow-sm border border-base-200">
        <div class="card-body">
            <h2 class="card-title text-xl mb-4">Daftar Antrean Review Dokter</h2>

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Nama Karyawan</th>
                            <th>Tanggal MCU</th>
                            <th>Dokumen MCU</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingReviews as $result)
                        <tr>
                            <td class="font-bold">{{ $result->participant->employee->name }}</td>
                            <td>{{ $result->participant->schedule->schedule_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ Storage::url($result->result_document) }}" target="_blank" class="btn btn-sm btn-outline btn-info">Lihat Dokumen</a>
                            </td>
                            <td>
                                <button wire:click="openReviewModal({{ $result->id }})" class="btn btn-sm btn-primary">Review Sekarang</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 italic text-base-content/50">Tidak ada data yang perlu di-review.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <flux:modal wire:model="showReviewModal" flyout variant="floating" class="md:w-lg">

        <flux:heading size="lg" class="border-b pb-2">Review Status MCU</flux:heading>

        <form wire:submit="saveReview" class="py-4 space-y-4 ">
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

            <flux:select size="xs" wire:model.live='fit_status' placeholder="Choose Status...">
                <option value="">-- Pilih Status Kebugaran --</option>
                <option value="fit_to_work">✅ Fit To Work</option>
                <option value="fit_with_notes">⚠️ Fit With Notes (Restriction)</option>
                <option value="temporary_unfit">⏳ Temporary Unfit</option>
                <option value="unfit">❌ Unfit</option>
            </flux:select>
            <x-label-error :messages="$errors->get('fit_status')" />

            @if($fit_status === 'fit_with_notes')
            <fieldset class="fieldset border border-base-300 p-3 rounded-lg bg-base-50/50">
                <x-form.label label="Kategori Penyakit Temuan (Bisa pilih > 1)" />

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-56 overflow-y-auto p-2 border border-base-200 rounded bg-base-100 items-center">
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

            @if($fit_status === 'temporary_unfit')
            <fieldset class="fieldset border border-base-300 p-3 rounded-lg bg-base-50/50">
                <x-form.label label="Kategori Penyakit Temuan (Bisa pilih > 1)" />

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-56 overflow-y-auto p-2 border border-base-200 rounded bg-base-100 items-center">
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

            <fieldset class="mb-4 fieldset md:col-span-2" wire:key="box-doctor_notes">
                <x-form.label label="Catatan Tambahan Dokter" required />
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



</section>
