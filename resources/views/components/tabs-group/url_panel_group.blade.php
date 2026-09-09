<div class="flex justify-between">
    <flux:tooltip content="tambah data" position="top">
        <flux:button size="xs" wire:click='open_modal' icon="add-icon" variant="primary"></flux:button>
    </flux:tooltip>
    <div>
        <div class="relative">
            <input type="text" wire:model.live.debounce.300ms="search_group"
                placeholder="Ketik untuk mencari Group..." {{-- 💡 Terapkan SEMUA class styling ke input --}}
                class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs pr-10" />
            {{-- Spinner diposisikan absolute di kanan input --}}
            <div wire:loading.remove.class='hidden' wire:target="search_group"
                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 hidden">
                <span class="loading loading-spinner loading-sm text-secondary"></span>
            </div>
        </div>
    </div>
</div>
