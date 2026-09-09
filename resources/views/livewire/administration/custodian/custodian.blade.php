<section class="w-full">
    <x-toast />
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
    @endpush
    @include('partials.kustodian-heading')

    <div class="flex justify-between">
        <flux:tooltip content="tambah data" position="top">
            <flux:button size="xs" wire:click='open_modal' icon="add-icon" variant="primary"></flux:button>
        </flux:tooltip>
        <div class='flex flex-col gap-2 md:flex-row'>
            <flux:input size='xs' icon="magnifying-glass" wire:model.blur='search_department' placeholder="Search departemen" />
        </div>
    </div>

    <x-manhours.layout>
        <div class="overflow-x-auto" wire:key="container-kustodian-table">
            <table class="table table-xs table-zebra">
                <thead class="text-center">
                    <tr>
                        <th># </th>
                        <th colspan="2">{{ __('Kustodian') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($Departments as $no => $dept)
                    {{-- Tambahkan wire:key pada baris utama --}}
                    <tr wire:key="dept-{{ $dept->id }}" class="even:bg-base-200 odd:bg-white hover:bg-accent/25">
                        <th class="text-center">{{ $Departments->firstItem() + $no }}</th>
                        <th>
                            <div class='flex justify-center'>
                                <span class="w-full max-w-40">{{$dept->department_name }}</span>
                            </div>
                        </th>
                        <th class='flex justify-center'>
                            @if ($dept->contractors()->count() > 0)
                            <table class='justify-items-center'>
                                <thead>
                                    <tr class="text-center">
                                        <th>{{ __('Nama Kontraktor') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dept->contractors()->get() as $contractor)
                                    {{-- Tambahkan wire:key pada baris dalam --}}
                                    <tr wire:key="contractor-{{ $dept->id }}-{{ $contractor->id }}" class="text-center">
                                        <td>{{ $contractor->contractor_name }}</td>
                                        <th class='flex flex-row justify-center gap-2'>
                                            <flux:tooltip content="edit" position="top" wire:key="tooltip-edit-{{ $contractor->id }}">
                                                <flux:button wire:key="btn-edit-{{ $dept->id }}-{{ $contractor->id }}" wire:click="modalEdit({{ $dept->id }}, {{ $contractor->id }})" size="xs" icon="pencil-square" variant="subtle"></flux:button>
                                            </flux:tooltip>

                                            <flux:tooltip content="hapus" position="top" wire:key="tooltip-del-{{ $contractor->id }}">
                                                {{-- Pastikan confirmDelete mengirim ID agar modal tahu apa yang dihapus --}}
                                                <flux:button wire:key="btn-delete-{{ $dept->id }}-{{ $contractor->id }}" wire:click="confirmDelete({{ $dept->id }}, {{ $contractor->id }})" size="xs" icon="trash" variant="danger" wire:loading.attr="disabled"></flux:button>
                                            </flux:tooltip>
                                        </th>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <p class="text-xs font-extrabold text-transparent capitalize bg-gradient-to-r from-rose-500 to-orange-500 bg-clip-text">{{ __('tidak ada kustodian') }}</p>
                            @endif
                        </th>
                    </tr>
                    @empty
                    <tr class="text-center">
                        <th colspan="3" class="font-semibold text-rose-500">not found !!!</th>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="text-center">
                    <tr>
                        <th>#</th>
                        <th colspan="2">{{ __('Kustodian') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="mt-1">{{ $Departments->links() }}</div>
    </x-manhours.layout>

    {{-- MODAL DELETE: Dipindah ke luar loop agar tidak duplikat di DOM --}}
    <flux:modal name="delete-custodian" class="min-w-[22rem]">
        <div class="space-y-6">
            <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm sm:justify-self-center">
                <legend class="fieldset-legend">
                    <flux:heading size="md">{{ __('Delete Kustodian') }}?</flux:heading>
                </legend>
                <flux:text color='accent'>
                    <p>You're about to delete this data {{ $custodian_id }}</p>
                    <p>This action cannot be reversed.</p>
                </flux:text>
            </fieldset>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                {{-- Gunakan variable yang di-set saat confirmDelete dipanggil --}}
                <flux:button wire:click='delete' size='xs' variant="danger">Delete</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL ADD --}}
    <flux:modal name="custodian">
        <form wire:submit='store' class='grid justify-items-stretch'>
            @csrf
            <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm sm:justify-self-center">
                <legend class="fieldset-legend">{{ $legend }}</legend>
                <x-label-req>{{ __('Nama Departemen') }} </x-label-req>
                <flux:select size="xs" wire:model.live="department_id" placeholder="Choose Department...">
                    @foreach ($Department as $department)
                    <flux:select.option value="{{ $department->id }}">{{ $department->department_name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <x-label-error :messages="$errors->get('department_id')" />

                <x-label-req>{{ __('Bisnis Unit') }} </x-label-req>
                <flux:select size="xs" wire:model.live="contractor_id" placeholder="Choose Contractor...">
                    @foreach ($Contractors as $contractor)
                    <flux:select.option value="{{ $contractor->id }}">{{ $contractor->contractor_name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <x-label-error :messages="$errors->get('contractor_id')" />

                <x-label-req>{{ __('Status') }} </x-label-req>
                <flux:select size="xs" wire:model.live="status" placeholder="Choose Status...">
                    <flux:select.option value="enabled">enabled</flux:select.option>
                    <flux:select.option value="disabled">disabled</flux:select.option>
                </flux:select>
                <x-label-error :messages="$errors->get('status')" />
            </fieldset>

            <div class="modal-action">
                <flux:button size="xs" type="submit" icon="save-icon" variant="primary">Save</flux:button>
                <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- MODAL EDIT --}}
    <flux:modal name="custodian-edit">
        <form wire:submit='store' class='grid justify-items-stretch'>
            @csrf
            <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm sm:justify-self-center">
                <legend class="fieldset-legend">{{ $legend }}</legend>
                <x-label-req>{{ __('Nama Departemen') }} </x-label-req>
                <flux:select size="xs" wire:model.live="department_id">
                    @foreach ($Department as $department)
                    <flux:select.option value="{{ $department->id }}">{{$department->department_name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <x-label-error :messages="$errors->get('department_id')" />

                <x-label-req>{{ __('Bisnis Unit') }} </x-label-req>
                <flux:select size="xs" wire:model.live="contractor_id">
                    @foreach ($Contractors as $contractor)
                    <flux:select.option value="{{ $contractor->id }}">{{ $contractor->contractor_name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <x-label-error :messages="$errors->get('contractor_id')" />

                <x-label-req>{{ __('Status') }} </x-label-req>
                <flux:select size="xs" wire:model.live="status">
                    <flux:select.option value="enabled">enabled</flux:select.option>
                    <flux:select.option value="disabled">disabled</flux:select.option>
                </flux:select>
                <x-label-error :messages="$errors->get('status')" />
            </fieldset>

            <div class="modal-action">
                <flux:button size="xs" type="submit" icon="save-icon" variant="primary">Save</flux:button>
                <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
