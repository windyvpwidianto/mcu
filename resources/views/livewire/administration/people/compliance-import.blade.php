<div>
    <x-button.btn-tooltip modalId="import_modal" color="accent" icon="file-import" tooltip="Import Data" />
    <dialog id="import_modal" class="modal" wire:ignore.self>
        <div class="modal-box">
            <h3 class="text-lg font-bold">Import Data Compliance</h3>
            <p class="text-[9px] font-bold text-gray-400 uppercase">Unggah file Excel (.xlsx, .xls) atau CSV yang berisi
                data manhours.</p>
                {{-- Input File --}}
                <div class="w-full my-4 form-control">
                    <label class="label">
                        <span class="label-text text-[9px]">Pilih File Import</span>
                    </label>
                    <input type="file" wire:model.live="file"
                        class="w-full file-input-xs focus-within:outline-none focus-within:border-info focus-within:ring-0 file-input file-input-bordered" />
                    {{-- Menampilkan error validasi Livewire --}}
                    @error('file')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                    @enderror
                </div>

                <div class="modal-action">
                    {{-- Tombol Tutup --}}
                    {{-- Gunakan wire:click="closeModal" agar state Livewire ($showModal) ikut di-update --}}
                    <form method="dialog">
                        <!-- if there is a button in form, it will close the modal -->
                        <button class="btn btn-soft btn-error btn-xs" wire:click="closeModal">Close</button>
                    </form>
                    {{-- Tombol Submit --}}
                    <button class="btn btn-xs btn-soft btn-success" wire:loading.attr="disabled" wire:click="import">
                        <span wire:loading.add.class='hidden' wire:target='import,file'><x-icon.file-import /></span>
                        <span wire:loading.remove.class='hidden' wire:target='import,file' class="hidden loading loading-bars loading-xs"></span>
                        <span wire:loading.add.class='hidden' wire:target='import,file'>Upload</span>
                        <span wire:loading.remove.class='hidden' wire:target='import,file' class="hidden">Proses Upload</span>
                    </button>
                </div>

        </div>
    </dialog>
</div>

