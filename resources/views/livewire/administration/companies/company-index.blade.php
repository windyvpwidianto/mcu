<section class="w-full">
    <x-toast />
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
    @include('partials.company-heading')
    <div class="flex justify-between">
        <div>
            <flux:tooltip content="tambah data" position="top">
                <flux:button size="xs" wire:click='open_modal' icon="add-icon" variant="primary"></flux:button>
            </flux:tooltip>
            {{-- <flux:tooltip content="upload" position="top">
                <flux:button size="xs" wire:click='open_modal_opload' icon="upload" variant="subtle"></flux:button>
            </flux:tooltip> --}}
        </div>
        <div >
                <x-form.input-floating label="Cari Perusahaan" model="search_company" />
        </div>
    </div>
    <x-manhours.layout>
        <div class="overflow-x-auto ">
            <table class="table table-xs">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Nama Perusahaan') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($Companies as $no => $company)
                        <tr>
                            <th>{{ $Companies->firstItem() + $no }}</th>
                            <th>{{ $company->company_name }}</th>
                            <th>{{ $company->status }}</th>
                            <th class='flex flex-row justify-center gap-2'>
                                <flux:tooltip content="edit" position="top">
                                    <flux:button wire:click="open_modal({{ $company->id }})" size="xs"
                                        icon="pencil-square" variant="subtle"></flux:button>
                                </flux:tooltip>
                                <flux:modal.trigger name="delete-company">
                                    <flux:tooltip content="hapus" position="top">
                                        <flux:button wire:click="showDelete({{ $company->id }})" size="xs"
                                            icon="trash" variant="danger"></flux:button>
                                    </flux:tooltip>
                                </flux:modal.trigger>
                            </th>
                        </tr>
                    @endforeach

                </tbody>
                <tfoot class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Nama Perusahaan') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-manhours.layout>
    <div class="mt-4">{{ $Companies->links() }}</div>
    <flux:modal name="company">
        <form wire:submit='store' class='grid justify-items-stretch'>
            @csrf
            <fieldset
                class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm sm:justify-self-center">
                <legend class="fieldset-legend">{{ $legend }}</legend>
                {{-- Nama Perusahaan --}}
                <x-label-req>{{ __('Nama Perusahaan') }} </x-label-req>
                <x-text-input wire:model.live='company_name' :error="$errors->get('company_name')" type="text"
                    placeholder="Nama Perusahaan" />
                <x-label-error :messages="$errors->get('company_name')" />
                {{-- Status --}}
                <x-label-req>{{ __('Status') }} </x-label-req>
                <flux:select size="xs" wire:model.live="status" placeholder="Choose Status...">
                    <flux:select.option value="enabled">enabled</flux:select.option>
                    <flux:select.option value="disabled">disabled</flux:select.option>
                </flux:select>
                <x-label-error :messages="$errors->get('status')" />
            </fieldset>

            <div class="modal-action">
                <flux:button size="xs" type="submit" icon="save-icon" variant="primary">Save</flux:button>
                <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close
                </flux:button>
            </div>
        </form>
    </flux:modal>
    <flux:modal name="upload" class="min-w-[22rem]">
        <form wire:submit='import' wire:target="import,upload_data" wire:loading.class="skeleton">
            @csrf
            <fieldset wire:target="import,upload_data" wire:loading.class="skeleton"
                class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm sm:justify-self-center">
                <legend class="fieldset-legend">Upload Data</legend>
                <flux:input size="xs" variant='outline' type="file" wire:model.live="upload_data"
                    label="Upload Nama Perusahaan" />
            </fieldset>

            <div class="modal-action">
                <flux:button size="xs" type="submit" wire:target="upload_data"
                    wire:loading.class="btn btn-disabled" icon="save-icon" variant="primary">Save</flux:button>
                <flux:button size="xs" wire:click='close_modal_upload' wire:target="upload_data"
                    wire:loading.class="btn btn-disabled" icon="close-icon" variant="danger">Close</flux:button>
            </div>
        </form>
    </flux:modal>
    <flux:modal name="delete-company" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete project?</flux:heading>
                <flux:text class="mt-2">
                    <p>You're about to delete this {{ $company_name }}.</p>
                    <p>This action cannot be reversed.</p>
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click='delete' size='xs' variant="danger">Delete </flux:button>
            </div>
        </div>
    </flux:modal>




</section>
