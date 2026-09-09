<section class="w-full">
    <x-toast />
    <x-tabs-relation.layout>



        <!-- Tombol tambah -->

        <flux:tooltip content="tambah data" position="top">
            <flux:button size="xs" wire:click="openModal(false)" icon="add-icon" variant="primary"></flux:button>
        </flux:tooltip>
        <!-- Tabel Role -->
        <div class="overflow-x-auto">
            <table class="table table-xs">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>Nama</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $no=> $role)
                    <tr class="text-center">
                         <th>{{ $roles->firstItem() + $no }}</th>
                        <td>{{ $role->name }}</td>
                        <td>
                            <!-- Edit -->

                            <div class="tooltip tooltip-right ">
                                <div class="tooltip-content ">
                                    <div class="animate-bounce text-warning text-xs font-black">Edit</div>
                                </div>
                                <flux:button wire:click="openModal(true, {{ $role->id }})" size="xs" icon="pencil-square" variant="subtle"></flux:button>
                            </div>

                            <!-- Delete -->
                            <div class="tooltip tooltip-right ">
                                <div class="tooltip-content ">
                                    <div class="animate-bounce text-error text-xs font-black">Delete</div>
                                </div>
                                <flux:button wire:click="delete({{ $role->id }})" wire:confirm.prompt="Yakin hapus role ini? ?\n\nKetik HAPUS Untuk mengonfirmasi|HAPUS" size="xs" icon="trash" variant="danger"></flux:button>
                            </div>

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $roles->links() }}
        </div>

        <!-- Modal DaisyUI -->
        <dialog id="roleModal" class="modal" @if($modalOpen) open @endif>
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">
                    {{ $isEditing ? 'Edit Role' : 'Tambah Role' }}
                </h3>

                <form wire:submit.prevent="{{ $isEditing ? 'update' : 'store' }}" class="space-y-3">
                    <fieldset class="fieldset">
                        <label class="block ">Nama</label>
                        <input type="text" wire:model="name" class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />
                        <x-label-error :messages="$errors->get('name')" />
                    </fieldset>
                    <div class="modal-action">
                        <flux:button type="submit" size="xs" icon:trailing="save" variant="primary"> {{ $isEditing ? 'Update' : 'Simpan' }}</flux:button>
                        <flux:button size="xs" wire:click='closeModal' icon:trailing="circle-x" variant="danger">Batal</flux:button>

                    </div>
                </form>
            </div>
        </dialog>
    </x-tabs-relation.layout>
</section>
