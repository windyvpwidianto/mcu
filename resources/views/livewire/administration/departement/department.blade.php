<section class="w-full">
    <x-toast />
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
    @include('partials.department-head')
    <div class="flex justify-between">
        <div>
            <flux:tooltip content="tambah data" position="top">
                <flux:button size="xs" wire:click='open_modal' icon="add-icon" variant="primary"></flux:button>
            </flux:tooltip>
            <flux:tooltip content="upload" position="top">
                <flux:button size="xs" wire:click='open_modal_opload' icon="upload" variant="subtle"></flux:button>
            </flux:tooltip>
        </div>
        <div class='flex flex-col gap-2 md:flex-row'>
             <x-form.input-floating label="Cari Departemen" model="search_department" />
        </div>
    </div>
    <x-manhours.layout>

        <div class="overflow-x-auto ">
            <table class="table table-xs">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Nama Department') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="text-center">


                    @forelse ($Departments as $no => $dept)
                    <tr>
                        <th>{{ $Departments->firstItem() + $no }}</th>
                        <th>{{ $dept->department_name }}</th>
                        <th>{{ $dept->status }}</th>
                        <th class='flex flex-row justify-center gap-2'>
                            <flux:tooltip content="edit" position="top">
                                <flux:button wire:click="open_modal({{ $dept->id }})" size="xs" icon="pencil-square" variant="subtle"></flux:button>
                            </flux:tooltip>
                            <flux:tooltip content="hapus" position="top">
                                <flux:button wire:click="delete({{ $dept->id }})" wire:confirm.prompt="Are you sure you want to delete {{ $dept->department_name }} ?\n\nType DELETE to confirm|DELETE" size="xs" icon="trash" variant="danger"></flux:button>
                            </flux:tooltip>
                        </th>
                    </tr>
                    @empty
                    <tr>
                        <th colspan="5" class="font-semibold text-rose-500">not found !!!</th>
                    </tr>
                    @endforelse

                </tbody>
                <tfoot class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Nama Department') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-manhours.layout>
    <div class="mt-1">{{ $Departments->links() }}</div>
    <flux:modal name="dept">
        <form wire:submit='store' class='grid justify-items-stretch'>
            @csrf
            <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm sm:justify-self-center">
                <legend class="fieldset-legend">{{ $legend }}</legend>
                {{-- Nama Departemen --}}
                <x-label-req>{{ __('Nama Departemen') }} </x-label-req>
                <x-text-input wire:model.live='department_name' :error="$errors->get('department_name')" type="text" placeholder="Nama Perusahaan" />
                <x-label-error :messages="$errors->get('department_name')" />

                {{-- Status --}}
                <x-label-req>{{ __('Status') }} </x-label-req>
                <flux:select size="xs" wire:model.live="status" placeholder="Choose Status...">
                    <flux:select.option value="Enabled">enabled</flux:select.option>
                    <flux:select.option value="Disabled">disabled</flux:select.option>
                </flux:select>
                <x-label-error :messages="$errors->get('status')" />
            </fieldset>

            <div class="modal-action">
                <flux:button size="xs" type="submit" icon="save-icon" variant="primary">Save</flux:button>
                <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close</flux:button>
            </div>
        </form>
    </flux:modal>
    <flux:modal name="upload" class="min-w-[22rem]">
        <form wire:submit='import' wire:target="import,upload_data" wire:loading.class="skeleton">
            @csrf
            <fieldset wire:target="import,upload_data" wire:loading.class="skeleton" class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm sm:justify-self-center">
                <legend class="fieldset-legend">Upload Data</legend>
                    <flux:input size="xs" variant='outline' type="file" wire:model.live="upload_data"  label="Upload Nama Perusahaan"  />
            </fieldset>

            <div class="modal-action">
                <flux:button size="xs" type="submit" wire:target="upload_data" wire:loading.class="btn btn-disabled" icon="save-icon" variant="primary">Save</flux:button>
                <flux:button size="xs" wire:click='close_modal_upload' wire:target="upload_data" wire:loading.class="btn btn-disabled" icon="close-icon" variant="danger">Close</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
