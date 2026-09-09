<div class="flex justify-between">
    <flux:tooltip content="tambah data" position="top">
        <flux:button size="xs" wire:click='create()' icon="add-icon" variant="primary"></flux:button>
    </flux:tooltip>
    <div>
        <div class="relative">
            <input type="text" wire:model.live.debounce.300ms="search"
                placeholder="Pencarian..." {{-- 💡 Terapkan SEMUA class styling ke input --}}
                class="w-full pr-10 input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />
            {{-- Spinner diposisikan absolute di kanan input --}}
            <div wire:loading.remove.class='hidden' wire:target="search_group"
                class="absolute inset-y-0 right-0 z-10 flex items-center hidden pr-3 pointer-events-none">
                <span class="loading loading-spinner loading-sm text-secondary"></span>
            </div>
        </div>
    </div>
</div>

