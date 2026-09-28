<div>
    <div class="grid items-start grid-cols-1 gap-6 max-w-7xl mx-auto">
        <section class="w-full px-4 pt-6">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Input Hasil MCU (Medical Admin)</h2>

                    @if (session()->has('message'))
                    <div class="alert alert-success mb-4">{{ session('message') }}</div>
                    @endif

                    <form wire:submit="saveResult" class="space-y-4">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="col-span-1 md:col-span-2">
                                <x-form.searchable-select-advanced label="Peserta MCU" placeholder="Cari Nama Peserta MCU..."
                                    modelsearch="searchParticipant" modelid="participant_id" :options="$formattedParticipants" :showdropdown="$showParticipantDropdown" clickaction="selectParticipant" />
                            </div>
                            
                            <fieldset class="w-full">
                                <x-form.label label="Kategori MCU" required />
                                <select wire:model="mcu_category" class="select select-bordered select-xs h-[32px] text-sm w-full">
                                    <option value="">Pilih Kategori...</option>
                                    <option value="New Hire">New Hire</option>
                                    <option value="Annual">Annual</option>
                                    <option value="Premedical">Premedical</option>
                                    <option value="Semesterly">Semesterly</option>
                                </select>
                                <x-label-error :messages="$errors->get('mcu_category')" />
                            </fieldset>

                            <div class="w-full">
                                <x-form.upload label="Unggah Dokumen Hasil (PDF/JPG)" model="result_document" :file="$result_document" required />
                            </div>
                        </div>

                        <fieldset class="mb-4 fieldset md:col-span-2" wire:key="box-admin_notes">
                            <x-form.label label="Catatan Admin (Opsional)" />
                            <div x-data="ckeditorHelper('admin_notes')" wire:ignore>
                                <div x-ref="editorElement" data-placeholder="{{ __('Masukkan Catatan Admin...') }}"></div>
                            </div>
                            <x-label-error :messages="$errors->get('admin_notes')" />
                        </fieldset>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <span wire:loading.remove wire:target="saveResult">Kirim ke Dokter</span>
                                <span wire:loading.remove.class="hidden" wire:target="saveResult"
                                    class="loading loading-spinner hidden"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
        <section class="w-full px-4 pb-6">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Daftar Peserta MCU (Menunggu Review Dokter)</h2>
                        <div class="overflow-x-auto w-full">
                            <table class="table table-zebra table-sm w-full">
                                <thead>
                                    <tr class="bg-base-200">
                                        <th>#</th>
                                        <th>Nama Peserta</th>
                                        <th>Jadwal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($formattedParticipants as $index => $participant)
                                    <tr>
                                        <td>{{ $formattedParticipants->firstItem() + $index }}</td>

                                        <td class="font-medium">{{ $participant->raw_name }}</td>

                                        <td class="whitespace-nowrap">{{ $participant->schedule_date }}</td>

                                        <td>
                                            <div class="flex flex-col gap-1 items-start">
                                                @if($participant->attendance === 'present')
                                                    <span class="badge badge-success badge-xs">Hadir</span>
                                                @elseif($participant->attendance === 'scheduled')
                                                    <span class="badge badge-warning badge-xs">Belum Hadir</span>
                                                @else
                                                    <span class="badge badge-neutral badge-xs">{{ ucfirst($participant->attendance) }}</span>
                                                @endif
                                                
                                                @if($participant->process === 'scheduled')
                                                    <span class="badge badge-info badge-xs text-[10px]">Menunggu Pelaksanaan</span>
                                                @else
                                                    <span class="badge badge-ghost badge-xs">{{ ucfirst($participant->process) }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-gray-500">Belum ada data peserta (atau jadwal sudah terlewat).</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $formattedParticipants->links() }}
                        </div>
                </div>
            </div>
        </section>
    </div>
</div>