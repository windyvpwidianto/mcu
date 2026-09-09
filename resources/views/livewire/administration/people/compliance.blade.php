<section class="w-full">
    <x-toast />
    <x-tabs-people.layout :idUser="$userId">

        <div class="flex flex-col items-center p-2 border rounded-t-lg md:flex-row md:justify-between border-neutral-200 ">
            <div class="flex gap-2 rounded">
                <x-button.btn-tooltip color="primary" icon="add" modalId="compliance_user_modal" tooltip="Tambah Data" />
                @livewire('administration.people.compliance-import')
            </div>
            <div class="flex gap-2 rounded">

            </div>
        </div>

        <div class="overflow-x-auto border">
            <table class="table table-xs">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Compliance Name</th>
                        <th>Start Date</th>
                        <th>Expiry Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compliances as $index => $item)
                    <?php

                    $tgl = Carbon\Carbon::parse($item->start_date)->format('d-m-Y');
                    $tgl_expiry = Carbon\Carbon::parse($item->expired_at)->format('d-m-Y');
                    ?>
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        {{-- Mengambil nama dari relasi master --}}
                        <td>{{ $item->master->name ?? 'N/A' }}</td>
                        <td>{{ $tgl}}</td>
                        <td>
                            @php
                            $status = $this->getExpiryStatus($item->expired_at);
                            @endphp

                            <span class="badge {{ $status['class'] }}">
                                {{ $status['label'] }}
                            </span>
                        </td>
                        <td class="gap-2">
                            <x-button.btn-tooltip wireClick="edit({{ $item->id }})" color="warning" icon="edit" tooltip="Details" />
                            <x-button.btn-tooltip wire:click="closed" color="error" icon="delete" modalId="delete_modal" tooltip="Hapus" />

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <dialog wire:ignore.self id="compliance_user_modal" class="modal">
            <div class="modal-box">
                <h3 class="mb-4 text-lg font-bold">
                    {{ $isEditMode ? 'Update Compliance' : 'Add New Compliance' }}
                </h3>
                <fieldset class="fieldset">
                    <x-form.label label="Pilih Class" required />
                    <select wire:model.live="compliance_class"
                        class="w-full select select-bordered select-xs focus-within:outline-none focus-within:border-info focus-within:ring-0">
                        <option value="">-- Select Existing Class --</option>
                        {{-- Loop data class yang unik dari database --}}
                        @foreach($this->existing_classes as $item)
                        <option value="{{ $item }}">{{ $item }}</option>
                        @endforeach
                    </select>
                    <x-label-error :messages="$errors->get('class')" />
                </fieldset>
                <fieldset class="fieldset">
                    <x-form.label label="Description" required />
                    <select wire:model.live="compliance_name"
                        class="w-full select select-bordered select-xs focus-within:outline-none focus-within:border-info focus-within:ring-0">
                        <option value="">-- Select Existing Compliance --</option>
                        @foreach($compliance_names as $name)
                        <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <x-label-error :messages="$errors->get('class')" />
                </fieldset>
                <fieldset class="relative fieldset">
                    <x-form.label label="Start Date" required />
                    <div
                        class="{{ $errors->has('tanggal') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500 rounded' : 'ring-base-300 focus:ring-base-300 focus:border-base-300 rounded' }}">
                        <div class="relative " wire:ignore x-data="{
                            fp: null,
                            initFlatpickr() {
                                if (this.fp) this.fp.destroy();
                                this.fp = flatpickr(this.$refs.tanggalInput, {
                                    disableMobile: true,
                                    enableTime: false,
                                    defaultDate: this.$wire.entangle('start_date').defer,
                                    dateFormat: 'd-m-Y ',
                                    clickOpens: true,
                                    // HAPUS ATAU KOMENTARI BARIS INI (appendTo)
                                    // appendTo: this.$refs.wrapper,
                                    static: true,
                                    // TAMBAHKAN ATAU UBAH OPSI POSITION
                                    position: 'auto-below', // Opsi ini akan memaksa kalender muncul di bawah input.

                                    onChange: (selectedDates, dateStr) => {
                                        this.$wire.set('start_date', dateStr);
                                    }
                                });
                            }
                        }" x-ref="wrapper"
                            x-init="initFlatpickr();
                            Livewire.hook('message.processed', () => {
                                initFlatpickr();
                            });">
                            <input type="text" x-ref="tanggalInput" wire:model.live='start_date'
                                placeholder="Start Date" readonly
                                class="input input-bordered cursor-pointer w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('tanggal') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                        </div>
                    </div>
                    <x-label-error :messages="$errors->get('start_date')" />
                </fieldset>
                <div class="modal-action">
                    <button
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="btn btn-xs btn-soft btn-success">
                        <span wire:loading.remove.class='hidden' wire:target="save" class="hidden loading loading-spinner loading-xs"></span>
                        {{ $isEditMode ? 'Update Data' : 'Save Data' }}
                    </button>
                    <form method="dialog">
                        <!-- if there is a button in form, it will close the modal -->
                        <button class="btn btn-xs btn-error btn-soft">Close</button>
                    </form>
                </div>
            </div>
        </dialog>
    </x-tabs-people.layout>
    <script>
        // Listener untuk membuka modal
        window.addEventListener('open-modal-compliance', event => {
            document.getElementById('compliance_user_modal').showModal();
        });

        // Listener untuk menutup modal
        window.addEventListener('close-modal-compliance', event => {
            document.getElementById('compliance_user_modal').close();
        });
    </script>
</section>
