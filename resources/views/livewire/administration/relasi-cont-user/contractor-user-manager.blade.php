<section class="w-full">
    <x-toast />
    <x-tabs-relation.layout>

        <fieldset class="mb-4 fieldset">
            <label class="block ">Kontraktor</label>
            <div class="relative" x-data @click.outside="$wire.set('showContractorDropdown', false)">
                <!-- Input pencarian -->
                <input type="text" wire:model.live="searchContractor" placeholder="Cari Contractor..." class="input input-bordered w-full input-xs focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden" wire:focus="$set('showContractorDropdown', true)" />
                <!-- Dropdown hasil search -->
                @if($showContractorDropdown && count($contractors) > 0)
                <ul class="absolute z-10 bg-base-100 border rounded-md w-full mt-1 max-h-60 overflow-auto shadow">
                    <!-- Spinner ketika klik -->
                    <div wire:loading wire:target="selectContractor" class="p-2 text-center">
                        <span class="loading loading-spinner loading-sm text-secondary"></span>
                    </div>
                    @foreach($contractors as $dept)
                    <li wire:click="selectContractor({{ $dept->id }}, '{{ $dept->contractor_name }}')" class="px-3 py-2 cursor-pointer hover:bg-base-200">
                        {{ $dept->contractor_name }}
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>

            @error('contractor_id') <span class="text-red-500">{{ $message }}</span> @enderror
        </fieldset>

        @if($contractor_id)
        <fieldset class="mb-4 fieldset">
            <label class="block font-medium">Pilih User</label>
            <div class="flex items-center space-x-2">
                <!-- Input pencarian -->
                <input type="text" wire:model.live.debounce.300ms="searchUser" placeholder="Cari User..." class="input input-bordered w-full max-w-xs focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />

                <!-- Checkbox filter -->
                <label class="flex items-center space-x-1">
                    <input type="checkbox" wire:model.live="showOnlySelected" class="checkbox checkbox-xs">
                    <span class="text-xs">Hanya terpilih</span>
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-1">
                @foreach($users as $user)
                @if(!$showOnlySelected || in_array($user->id, $selectedUsers))
                <label class="flex items-center space-x-2" wire:key="user-{{ $user->id }}">
                    <input type="checkbox" wire:click="toggleUser({{ $user->id }})" @if(in_array($user->id, $selectedUsers)) checked @endif
                    class="checkbox checkbox-xs">
                    <span>{{ $user->name }}</span>
                </label>
                @endif
                @endforeach
            </div>
            <div class="mt-4">
                @unless($showOnlySelected)
                {{ $users->links() }}
                @endunless
            </div>
        </fieldset>

        <button wire:click="save" class="btn btn-sm btn-success">Simpan Relasi</button>
        @endif
    </x-tabs-relation.layout>
</section>
