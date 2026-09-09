<div>
    @include('partials.cause-analysis-heading')
    <x-toast />
    <x-tabs-cause-analysis.layout>

        <!-- Tombol Tambah KTA -->
        <div class="tooltip tooltip-right ">
            <div class="tooltip-content ">
                <div class="animate-bounce text-orange-400 text-sm font-black">Tambah TTA</div>
            </div>
            <flux:button size="xs" wire:click='create' icon="add-icon" variant="primary"></flux:button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto mt-2">
            <table class="table table-xs">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ttas as $i => $kta)
                    <tr>
                        <th>{{ $i+1 }}</th>
                        <td>{{ $kta->name }}</td>
                        <td>{{ $kta->status }}</td>
                        <td class="flex gap-2">
                            <!-- Edit -->

                            <div class="tooltip tooltip-right ">
                                <div class="tooltip-content ">
                                    <div class="animate-bounce text-warning text-xs font-black">Edit</div>
                                </div>
                                <flux:button wire:click="edit({{ $kta->id }})" size="xs" icon="pencil-square" variant="subtle"></flux:button>
                            </div>

                            <!-- Delete -->
                            <div class="tooltip tooltip-right ">
                                <div class="tooltip-content ">
                                    <div class="animate-bounce text-error text-xs font-black">Delete</div>
                                </div>
                                <flux:button wire:click="delete({{ $kta->id }})" wire:confirm.prompt="Are you sure you want to delete menu ?\n\nType DELETE to confirm|DELETE" size="xs" icon="trash" variant="danger"></flux:button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Modal (pakai div, bukan dialog) -->
        @if($modalOpen)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-base-100 rounded-lg shadow-lg w-full max-w-md p-4">
                <h3 class="font-bold text-md mb-3">{{ $ttaId ? 'Edit TTA' : 'Tambah TTA' }} </h3>

                <fieldset class="fieldset">
                    <label class="block ">Nama</label>
                    <input type="text" wire:model="name" class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />
                    <x-label-error :messages="$errors->get('name')" />
                </fieldset>
                <fieldset class="fieldset">

                    <fieldset class="fieldset">
                        <label class="block ">Status</label>
                        <select wire:model="status" class="select select-xs select-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden">
                            <option value="">-- Pilih Status --</option>
                            <option value="Enabled">Enabled</option>
                            <option value="Disable">Disable</option>
                        </select>
                        <x-label-error :messages="$errors->get('status')" />
                    </fieldset>


                    <div class="flex justify-end gap-2 mt-4">
                         <flux:button size="xs" wire:click='closeModal' icon:trailing="circle-x" variant="danger">Batal</flux:button>
                        @if($ttaId)
                        <flux:button size="xs" wire:click='update' icon:trailing="save" variant="primary">Update</flux:button>
                        @else
                        <flux:button size="xs" wire:click='store' icon:trailing="save" variant="primary">Simpan</flux:button>
                        @endif
                    </div>
            </div>
        </div>
        @endif

    </x-tabs-cause-analysis.layout>
</div>
