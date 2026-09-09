<section class="w-full">
    <x-toast />
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
    @include('partials.contractor-heading')
    <div class="flex justify-between">
        <flux:tooltip content="tambah data" position="top">
            <flux:button size="xs" wire:click='open_modal' icon="add-icon" variant="primary"></flux:button>
        </flux:tooltip>
        <div>
            <flux:input size='xs' icon="magnifying-glass" wire:model.live='search_contractor' placeholder="Search contractor" />
        </div>
    </div>
    <x-manhours.layout>
        <div class="overflow-x-auto ">
            <table class="table table-xs">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Nama Kontraktor') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($Contractors as $no => $contractor)
                    <tr>
                        <th>{{ $Contractors->firstItem() + $no }}</th>
                        <th>{{ $contractor->contractor_name }}</th>
                        <th>{{ $contractor->status }}</th>
                        <th class='flex justify-center flex-row gap-2'>
                            <flux:tooltip content="edit" position="top">
                                <flux:button wire:click="open_modal({{ $contractor->id }})" size="xs" icon="pencil-square" variant="subtle"></flux:button>
                            </flux:tooltip>
                            <flux:modal.trigger name="delete-contractor">
                                <flux:tooltip content="hapus" position="top">
                                    <flux:button wire:click="showDelete({{ $contractor->id }})" size="xs" icon="trash" variant="danger"></flux:button>
                                </flux:tooltip>
                            </flux:modal.trigger>
                        </th>
                    </tr>
                    @endforeach

                </tbody>
                <tfoot class="text-center">
                    <tr>
                        <th>#</th>
                          <th>{{ __('Nama Kontraktor') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-manhours.layout>
    <div class="mt-4">{{ $Contractors->links() }}</div>
    <flux:modal name="contractor">
        <form wire:submit='store' class='grid justify-items-stretch'>
            @csrf
            <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm border p-4 sm:justify-self-center">
                <legend class="fieldset-legend">{{ $legend }}</legend>
                {{-- Nama Perusahaan --}}
                <x-label-req>{{ __('Nama Perusahaan') }} </x-label-req>
                <x-text-input wire:model.live='contractor_name' :error="$errors->get('contractor_name')" type="text" placeholder="Nama Perusahaan" />
                <x-label-error :messages="$errors->get('contractor_name')" />
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
    <flux:modal name="delete-contractor" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete project?</flux:heading>
                <flux:text class="mt-2">
                    <p>You're about to delete this {{ $contractor_name }}.</p>
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
