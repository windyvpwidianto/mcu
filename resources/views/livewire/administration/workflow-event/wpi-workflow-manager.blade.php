<section class="w-full">
    {{-- Toast Notification --}}
    <x-toast />

    <x-tabs-workflow-event.layout :activeTab="$activeTab" :heading="$heaading" :subheading="$subheading">
        {{-- Slot untuk Tombol Tambah (Biasanya diletakkan di bagian atas tabel) --}}
        <div class="flex justify-end mb-4">
            <flux:button wire:click="openModal" variant="primary" icon="plus" size="sm">
                Tambah Workflow
            </flux:button>
        </div>

        <div class="overflow-x-auto bg-white rounded-lg shadow-md md:overflow-hidden">
            <table class="table w-full min-w-full table-xs">
                <thead>
                    <tr>
                        <th class="text-xs font-semibold tracking-wider text-left text-gray-600 uppercase bg-gray-100 border-b-2 border-gray-200">
                            Dari Status (Key)
                        </th>
                        <th class="text-xs font-semibold tracking-wider text-left text-gray-600 uppercase bg-gray-100 border-b-2 border-gray-200">
                            Dari Status (Nama)
                        </th>
                        <th class="text-xs font-semibold tracking-wider text-left text-gray-600 uppercase bg-gray-100 border-b-2 border-gray-200">
                            Ke Status (Key)
                        </th>
                        <th class="text-xs font-semibold tracking-wider text-left text-gray-600 uppercase bg-gray-100 border-b-2 border-gray-200">
                            Ke Status (Nama)
                        </th>
                        <th class="text-xs font-semibold tracking-wider text-left text-gray-600 uppercase bg-gray-100 border-b-2 border-gray-200">
                            Role
                        </th>
                        <th class="text-xs font-semibold tracking-wider text-center text-gray-600 uppercase bg-gray-100 border-b-2 border-gray-200">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($workflows as $workflow)
                        <tr wire:key="{{ $workflow->id }}">
                            <td class="bg-white border-b border-gray-200">
                                <span class="badge badge-sm {{ $this->getRandomBadgeColor($workflow->from_status) }} font-medium">
                                    {{ $workflow->from_status }}
                                </span>
                            </td>
                            <td class="text-sm bg-white border-b border-gray-200">
                                {{ $workflow->from_inisial }}
                            </td>
                            <td class="bg-white border-b border-gray-200">
                                <span class="badge badge-sm {{ $this->getRandomBadgeColor($workflow->to_status) }} font-medium">
                                    {{ $workflow->to_status }}
                                </span>
                            </td>
                            <td class="text-sm bg-white border-b border-gray-200">
                                {{ $workflow->to_inisial }}
                            </td>
                            <td class="text-sm bg-white border-b border-gray-200">
                                <span class="relative inline-block px-3 py-1 font-semibold leading-tight {{ strtolower($workflow->role) == 'moderator' ? 'text-purple-900 bg-purple-200' : 'text-indigo-900 bg-indigo-200' }} rounded-full text-xs">
                                    {{ ucfirst($workflow->role) }}
                                </span>
                            </td>
                            <th class='flex flex-row justify-center gap-2 bg-white border-b border-gray-200'>
                                <flux:tooltip content="edit" position="top">
                                    <flux:button wire:click="edit({{ $workflow->id }})" size="xs"
                                        icon="pencil-square" variant="subtle"></flux:button>
                                </flux:tooltip>

                                <flux:tooltip content="hapus" position="top">
                                    <flux:button wire:click="delete({{ $workflow->id }})" size="xs" icon="trash"
                                        wire:confirm="Apakah Anda yakin ingin menghapus workflow ini?"
                                        variant="danger"></flux:button>
                                </flux:tooltip>
                            </th>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-gray-500">Data workflow tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $workflows->links() }}
        </div>

        {{-- Modal CRUD --}}
        @if ($isModalOpen)
            <div class="modal modal-open" role="dialog">
                <div class="relative max-w-lg modal-box">
                    {{-- Tombol Close (x) --}}
                    <button type="button" wire:click="closeModal"
                        class="absolute btn btn-sm btn-circle btn-ghost right-2 top-2">✕</button>

                    <h3 class="flex items-center gap-2 mb-6 text-lg font-bold">
                        <flux:icon icon="{{ $workflowId ? 'pencil-square' : 'plus' }}" variant="outline" />
                        {{ $workflowId ? 'Edit WPI Workflow' : 'Tambah WPI Workflow Baru' }}
                    </h3>

                    <form wire:submit.prevent="save">
                        <div class="space-y-4">
                            {{-- Dari Status --}}
                            <div class="grid grid-cols-2 gap-4">
                                <flux:select label="Dari Status (Key)" id="from_status" wire:model="from_status"
                                    placeholder="Pilih Status..." size="xs">
                                    @foreach ($statusOptions as $status)
                                        <flux:select.option value="{{ $status }}">{{ $status }}</flux:select.option>
                                    @endforeach
                                </flux:select>

                                <flux:input label="Dari Status (Nama)" id="from_inisial" type="text"
                                    wire:model="from_inisial" placeholder="Cth: Submitted" size="xs" />
                            </div>

                            {{-- Ke Status --}}
                            <div class="grid grid-cols-2 gap-4">
                                <flux:select label="Ke Status (Key)" id="to_status" wire:model="to_status"
                                    placeholder="Pilih Status..." size="xs">
                                    @foreach ($statusOptions as $status)
                                        <flux:select.option value="{{ $status }}">{{ $status }}</flux:select.option>
                                    @endforeach
                                </flux:select>

                                <flux:input label="Ke Status (Nama)" id="to_inisial" type="text"
                                    wire:model="to_inisial" placeholder="Cth: Assigned" size="xs" />
                            </div>

                            {{-- Role --}}
                            <flux:select label="Role Yang Bertanggung Jawab" id="role"
                                placeholder="Pilih Role..." wire:model="role" size="xs">
                                @foreach ($roleOptions as $r)
                                    <flux:select.option value="{{ $r }}">{{ ucfirst($r) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        {{-- Modal Actions --}}
                        <div class="flex justify-end gap-2 mt-8">
                            <flux:button type="button" wire:click="closeModal" variant="ghost" size="sm">
                                Batal
                            </flux:button>
                            <flux:button type="submit" variant="primary" size="sm">
                                {{ $workflowId ? 'Update' : 'Simpan' }}
                            </flux:button>
                        </div>
                    </form>
                </div>
                {{-- Backdrop klik luar untuk menutup --}}
                <div class="modal-backdrop" wire:click="closeModal"></div>
            </div>
        @endif
    </x-tabs-workflow-event.layout>
</section>
