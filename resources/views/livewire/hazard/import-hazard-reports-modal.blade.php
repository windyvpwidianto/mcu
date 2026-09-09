<div>
    <!-- Tombol buka modal -->
    <div class="tooltip tooltip-right " data-tip="Import Hazard Reports">
        <flux:button icon="upload" variant="primary" class="hidden" size='xs' wire:click="openModal"></flux:button>
    </div>
    <!-- Modal -->
    <flux:modal wire:model="open" title="Import Data Hazard Reports" size="md">
        <div class="space-y-4">
            <div>
                <flux:input type="file" wire:model="file"
                    label="Unggah file Excel (.xlsx / .csv) sesuai format header hazard report." />
                @error('file')
                    <p class="text-sm text-red-500 mt-1">{{ $message }}</p>
                @enderror

                <div wire:loading.class.remove='hidden' wire:target="file" class="text-sm text-blue-500 mt-2 hidden">
                    Mengunggah file...
                </div>
            </div>
        </div>

        <div class="flex">
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeModal">
                    Batal
                </flux:button>

                <flux:button variant="primary" wire:click="import" wire:loading.attr="disabled">
                    <span wire:loading.remove>Import</span>
                    <span class="hidden" wire:loading.class.remove="hidden">Memproses...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
