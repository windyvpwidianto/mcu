<div>
    <div class="grid items-start grid-cols-1 gap-6 lg:grid-cols-2">
        <section class="w-full  mx-auto px-4 py-6">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Input Hasil MCU (Medical Admin)</h2>

                    @if (session()->has('message'))
                    <div class="alert alert-success mb-4">{{ session('message') }}</div>
                    @endif

                    <form wire:submit="saveResult" class="space-y-4">

                        <div class="flex  gap-4">
                            <x-form.searchable-select-advanced label="Peserta MCU" placeholder="Cari Nama Peserta MCU..."
                                modelsearch="searchParticipant" modelid="participant_id" :options="$formattedParticipants" :showdropdown="$showParticipantDropdown" clickaction="selectParticipant" />
                            <x-form.upload label="Unggah Dokumen Hasil (PDF/JPG)" model="result_document" :file="$result_document" required />
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
        <section class="w-full mx-auto px-4 py-6">
            <div class="card bg-base-100 shadow-sm border border-base-200">
                <div class="card-body">
                    <h2 class="card-title text-xl mb-4">Daftar Peserta MCU (Menunggu Review Dokter)</h2>
                    <x-mcu.layout>
                        <div class="overflow-x-auto">
                            <table class="table table-zebra table-sm">
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

                                        <td>{{ $participant->schedule_date }}</td>

                                        <td>
                                            {{-- Contoh pembuatan badge yang dinamis, atau gunakan bawaan Anda yang statis --}}
                                            <span class="badge badge-warning badge-xs">
                                                Belum Hadir
                                            </span>
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
                    </x-mcu.layout>
                </div>
            </div>
        </section>
    </div>
</div>