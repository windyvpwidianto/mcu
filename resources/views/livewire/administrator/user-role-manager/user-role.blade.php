<section class="w-full">
    <x-toast />
    <x-tabs-relation.layout>

        @if (session()->has('message'))
        <div class="alert alert-success my-2">
            {{ session('message') }}
        </div>
        @endif
        <!-- Search Bar -->
        <div class="mb-4 flex flex-col md:flex-row gap-2 md:items-center md:justify-between">
            <!-- Search Input -->
            <flux:input icon="magnifying-glass" size='xs' wire:model..live="search" placeholder="Cari nama atau email..." class=" input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden  " />
            <!-- Filter by Role -->
           
                <select wire:model.live="roleFilter" class="select select-xs md:select-xs select-bordered w-full md:max-w-md focus:ring-1 focus:border-info focus:ring-info focus:outline-none">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
           
        </div>
        <div class="overflow-x-auto">
            <table class="table table-xs w-full">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr class="text-xs">
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            {{ $user->roles->pluck('name')->join(', ') ?: '-' }}
                        </td>
                        <td>

                            <flux:button size="xs" wire:click="openModal({{ $user->id }})" icon:trailing="shild-user" variant="primary">Atur Role</flux:button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Modal DaisyUI -->
        <dialog id="roleModal" class="modal" @if($showModal) open @endif>
            <div class="modal-box">
                <h3 class="font-bold text-lg">Atur Role untuk {{ $selectedUser?->name }}</h3>
                <div class="py-4 space-y-2">
                    @foreach ($roles as $role)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="checkbox" wire:model="selectedRoles" value="{{ $role->id }}">
                        <span>{{ ucfirst($role->name) }}</span>
                    </label>
                    @endforeach
                </div>
                <div class="modal-action">
                    <button class="btn btn-success" wire:click="save">Simpan</button>
                    <button class="btn" wire:click="$set('showModal', false)">Batal</button>
                </div>
            </div>
        </dialog>

    </x-tabs-relation.layout>
    <div class="">
        {{ $users->links() }} {{-- Pagination links --}}
    </div>
</section>
