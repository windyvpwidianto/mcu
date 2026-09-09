<section class="w-full">
    <x-toast />
    <x-tabs-relation.layout>
        <fieldset class="mb-4 fieldset">
            <label class="block ">Departemen</label>
            <div class="relative w-full md:max-w-sm" x-data @click.outside="$wire.set('showDepartmentDropdown', false)">

                <!-- Input pencarian -->
                <div class="relative ">
                        <input type="text" wire:model.live.debounce.300ms="searchDepartment" wire:focus="$set('showDepartmentDropdown', true)"
                            placeholder="Ketik untuk mencari dan memilih Departemen..." {{-- 💡 Terapkan SEMUA class styling ke input --}}
                            class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs pr-10" />
                        {{-- Spinner diposisikan absolute di kanan input --}}
                        <div wire:loading.remove.class='hidden'  wire:target="searchDepartment,selectDepartment"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 hidden">
                            <span class="loading loading-spinner loading-sm text-secondary"></span>
                        </div>
                    </div>
                <!-- Dropdown hasil search -->
                @if($showDepartmentDropdown && count($departments) > 0)
                <ul class="absolute z-10 bg-base-100 border rounded-md w-full mt-1 max-h-60 overflow-auto shadow">
                    <!-- Spinner ketika klik -->
                    @foreach($departments as $dept)
                    <li wire:click="selectDepartment({{ $dept->id }}, '{{ $dept->department_name }}')" class="px-3 py-2 cursor-pointer hover:bg-base-200">
                        {{ $dept->department_name }}
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>

            @error('department_id') <span class="text-red-500">{{ $message }}</span> @enderror
        </fieldset>

        @if($department_id)
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

            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 ">
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
