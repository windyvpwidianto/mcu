<section class="w-full">
    <x-toast />
    @include('partials.location-heading')
    <div class="flex justify-between">
        <div>
            <flux:tooltip content="tambah data" position="top">
                <flux:button size="xs" wire:click='open_modal' icon="add-icon" variant="primary"></flux:button>
            </flux:tooltip>
            <flux:tooltip content="upload" position="top">
                <flux:button size="xs" wire:click='open_modal_opload' icon="upload" variant="subtle"></flux:button>
            </flux:tooltip>
        </div>
        <div>
            <flux:input class="w-full input-bordered input-xs focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden" size='xs' icon="magnifying-glass" wire:model.live='search_lokasi' placeholder="Search lokasi" />
        </div>
    </div>
    <x-manhours.layout>
        <div class="overflow-x-auto ">
            <table class="table table-xs">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Nama Lokasi') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($location as $no => $loc)
                    <tr>
                        <th>{{ $location->firstItem() + $no }}</th>
                        <th>{{ $loc->name }}</th>
                        <th>{{ $loc->status }}</th>
                        <th class='flex flex-row justify-center gap-2'>
                            <flux:tooltip content="edit" position="top">
                                <flux:button wire:click="open_modal({{ $loc->id }})" size="xs" icon="pencil-square" variant="subtle"></flux:button>
                            </flux:tooltip>
                            <flux:modal.trigger name="delete-lokasi">
                                <flux:tooltip content="hapus" position="top">
                                    <flux:button wire:click="showDelete({{ $loc->id }})" size="xs" icon="trash" variant="danger"></flux:button>
                                </flux:tooltip>
                            </flux:modal.trigger>
                        </th>
                    </tr>
                    @endforeach

                </tbody>
                <tfoot class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Nama Lokasi') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <flux:modal name="lokasi">
            <form wire:submit='store' class='grid justify-items-stretch'>
                @csrf
                <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm sm:justify-self-center">
                    <legend class="fieldset-legend">{{ $legend }}</legend>
                    {{-- Nama Lokasi --}}
                    <x-label-req>{{ __('Nama Lokasi') }} </x-label-req>
                    <x-text-input wire:model.live='lokasi_name' :error="$errors->get('lokasi_name')" type="text" placeholder="Nama Lokasi" />
                    <x-label-error :messages="$errors->get('lokasi_name')" />
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
                    <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close</flux:button>
                </div>
            </form>
        </flux:modal>
        <flux:modal name="upload" class="min-w-[22rem]">
            <form wire:submit='import' wire:target="import,upload_data" wire:loading.class="skeleton">
                @csrf
                <fieldset wire:target="import,upload_data" wire:loading.class="skeleton" class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm sm:justify-self-center">
                    <legend class="fieldset-legend">Upload Data</legend>
                    <flux:input size="xs" variant='outline' type="file" wire:model.live="upload_data" label="Upload Nama Lokasi" />
                </fieldset>

                <div class="modal-action">
                    <flux:button size="xs" type="submit" wire:target="upload_data" wire:loading.class="btn btn-disabled" icon="save-icon" variant="primary">Save</flux:button>
                    <flux:button size="xs" wire:click='close_modal_upload' wire:target="upload_data" wire:loading.class="btn btn-disabled" icon="close-icon" variant="danger">Close</flux:button>
                </div>
            </form>
        </flux:modal>
        <flux:modal name="delete-lokasi" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Delete project?</flux:heading>
                    <flux:text class="mt-2">
                        <p>You're about to delete this {{ $lokasi_name }}.</p>
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
    </x-manhours.layout>
     <div class="mt-1">{{ $location->links() }}</div>
</section>
