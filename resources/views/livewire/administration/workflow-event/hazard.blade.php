<section class="w-full">
    <x-toast />
    <x-tabs-workflow-event.layout :activeTab="$activeTab" :heading="$heaading" :subheading="$subheading">
        <div class="overflow-x-auto bg-white rounded-lg shadow-md md:overflow-hidden">
            <table class="table w-full min-w-full table-xs">
                <thead>
                    <tr>
                        <th
                            class="text-xs font-semibold tracking-wider text-left text-gray-600 uppercase bg-gray-100 border-b-2 border-gray-200 ">
                            Dari Status (Key)
                        </th>
                        <th
                            class="text-xs font-semibold tracking-wider text-left text-gray-600 uppercase bg-gray-100 border-b-2 border-gray-200 ">
                            Dari Status (Nama)
                        </th>
                        <th
                            class="text-xs font-semibold tracking-wider text-left text-gray-600 uppercase bg-gray-100 border-b-2 border-gray-200 ">
                            Ke Status (Key)
                        </th>
                        <th
                            class="text-xs font-semibold tracking-wider text-left text-gray-600 uppercase bg-gray-100 border-b-2 border-gray-200 ">
                            Ke Status (Nama)
                        </th>
                        <th
                            class="text-xs font-semibold tracking-wider text-left text-gray-600 uppercase bg-gray-100 border-b-2 border-gray-200 ">
                            Role
                        </th>
                        <th
                            class="text-xs font-semibold tracking-wider text-left text-gray-600 uppercase bg-gray-100 border-b-2 border-gray-200 ">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($workflows as $workflow)
                        <tr wire:key="{{ $workflow->id }}">
                            <td class="bg-white border-b border-gray-200 "> <span
                                    class="badge badge-sm badge-soft {{ $this->getRandomBadgeColor($workflow->from_status) }}">{{ $workflow->from_status }}</span>
                            </td>
                            <td class="text-sm bg-white border-b border-gray-200 ">
                                {{ $workflow->from_inisial }}</td>
                            <td class="bg-white border-b border-gray-200 "><span
                                    class="badge badge-sm badge-soft {{ $this->getRandomBadgeColor($workflow->to_status) }}">{{ $workflow->to_status }}</span>
                            </td>
                            <td class="text-sm bg-white border-b border-gray-200 ">{{ $workflow->to_inisial }}
                            </td>
                            <td class="text-sm bg-white border-b border-gray-200 ">
                                <span
                                    class="relative inline-block px-3 py-1 font-semibold leading-tight {{ $workflow->role == 'moderator' ? 'text-purple-900 bg-purple-200' : 'text-indigo-900 bg-indigo-200' }} rounded-full">
                                    {{ ucfirst($workflow->role) }}
                                </span>
                            </td>
                            <th class='flex flex-row justify-center gap-2'>
                                <flux:tooltip content="edit" position="top">
                                    <flux:button wire:click="edit({{ $workflow->id }})" size="xs"
                                        icon="pencil-square" variant="subtle"></flux:button>
                                </flux:tooltip>

                                <flux:tooltip content="hapus" position="top">
                                    <flux:button wire:click="delete({{ $workflow->id }})" size="xs" icon="trash"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus workflow ini?')"
                                        variant="danger"></flux:button>
                                </flux:tooltip>
                            </th>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $workflows->links() }}
        </div>

        {{-- Karena Livewire V3 memiliki fitur 'Livewire Modals', kita bisa menggunakan properti $isModalOpen --}}
        @if ($isModalOpen)
            {{-- DaisyUI Modal - Note: Kita menggunakan 'fixed inset-0' untuk overlay background Livewire secara manual karena kita memicu modal melalui state $isModalOpen --}}
            <div class="modal modal-open" role="dialog">
                <div class="relative modal-box" x-data="{}"
                    @click.away="window.livewire.find('{{ $this->getName() }}').closeModal()">

                    {{-- Tombol Close (x) di sudut kanan atas --}}
                    <form method="dialog">
                        <button type="button" wire:click="closeModal()"
                            class="absolute btn btn-sm btn-circle btn-ghost right-2 top-2">✕</button>
                    </form>

                    <h3 class="mb-4 text-lg font-bold">
                        {{ $workflowId ? 'Edit Hazard Workflow' : 'Tambah Hazard Workflow Baru' }}
                    </h3>

                    <form wire:submit.prevent="save">
                        <div class="space-y-4">

                            <div>
                                {{-- Menggunakan Flux Select Component --}}
                                <flux:select label="Dari Status (Key)" id="from_status" wire:model.defer="from_status"
                                    placeholder="Pilih Status Awal..." size="xs" {{-- Mengatur ukuran ke xs --}}>
                                    @foreach ($statusOptions as $status)
                                        <flux:select.option value="{{ $status }}">{{ $status }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>

                            </div>

                            <div>
                                {{-- Menggunakan Flux Input Component --}}
                                <flux:input label="Dari Status (Nama)" id="from_inisial" type="text"
                                    wire:model.defer="from_inisial" placeholder="Cth: Submitted Event" size="xs"
                                    {{-- Mengatur ukuran ke xs --}} />

                            </div>

                            <div>
                                {{-- Menggunakan Flux Select Component --}}
                                <flux:select label="Ke Status (Key)" id="to_status" wire:model.defer="to_status"
                                    placeholder="Pilih Status Tujuan..." size="xs" {{-- Mengatur ukuran ke xs --}}>
                                    @foreach ($statusOptions as $status)
                                        <flux:select.option value="{{ $status }}">{{ $status }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>

                            </div>

                            <div>
                                {{-- Menggunakan Flux Input Component --}}
                                <flux:input label="Ke Status (Nama)" id="to_inisial" type="text"
                                    wire:model.defer="to_inisial" placeholder="Cth: Moderator Review" size="xs"
                                    {{-- Mengatur ukuran ke xs --}} />

                            </div>

                            <div>
                                {{-- Menggunakan Flux Select Component --}}
                                <flux:select label="Role Yang Bertanggung Jawab" id="role"
                                    placeholder="Pilih Role..." wire:model.defer="role" size="xs"
                                    {{-- Mengatur ukuran ke xs --}}>
                                    @foreach ($roleOptions as $r)
                                        <flux:select.option value="{{ $r }}">{{ ucfirst($r) }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>
                        </div>

                        {{-- Modal Actions (Button) --}}
                        <div class="mt-6 modal-action">
                            <button type="button" wire:click="closeModal()" class="btn btn-sm btn-error btn-outline">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-success btn-sm btn-outline">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </x-tabs-workflow-event.layout>
</section>
