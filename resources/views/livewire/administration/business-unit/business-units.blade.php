<section class="w-full">
    <x-toast />
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
    @include('partials.bu')
    <div class="flex justify-between">
        <flux:tooltip content="tambah data" position="top">
            <flux:button size="xs" wire:click='open_modal' icon="add-icon" variant="primary"></flux:button>
        </flux:tooltip>
        <div>
            <flux:input size='xs' icon="magnifying-glass" wire:model.live='search_company' placeholder="Search company" />
        </div>
    </div>
    <x-manhours.layout>
        <div class="overflow-x-auto ">
            <table class="table table-xs">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Nama Perusahaan') }}</th>
                        <th>{{ __('Bisnis Unit') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($business_unit as $no => $bu)
                    <tr>
                        <th>{{ $business_unit->firstItem() + $no }}</th>
                        <th>{{ $bu->company_name }}</th>
                        <th>{{ $bu->companies->company_name }}</th>
                        <th>{{ $bu->status }}</th>
                        <th class='flex justify-center flex-row gap-2'>
                            <flux:tooltip content="edit" position="top">
                                <flux:button wire:click="open_modal({{ $bu->id }})" size="xs" icon="pencil-square" variant="subtle"></flux:button>
                            </flux:tooltip>
                            <flux:modal.trigger name="delete-bu">
                                <flux:tooltip content="hapus" position="top">
                                    <flux:button wire:click="showDelete({{ $bu->id }})" size="xs" icon="trash" variant="danger"></flux:button>
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
                        <th>{{ __('Bisnis Unit') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-manhours.layout>
    <div class="mt-4">{{ $business_unit->links() }}</div>
    <flux:modal name="bu">
        <form wire:submit='store' class='grid justify-items-stretch'>
            @csrf
            <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm border p-4 sm:justify-self-center">
                <legend class="fieldset-legend">{{ $legend }}</legend>
                {{-- Bussiness Unit --}}
                <x-label-req>{{ __('Bisnis Unit') }} </x-label-req>
                <flux:select size="xs" wire:model.live="company_id" placeholder="Choose Status...">
                    @foreach ($Companies as $comp)
                    <flux:select.option value="{{ $comp->id }}">{{ $comp->company_name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <x-label-error :messages="$errors->get('company_id')" />
                {{-- Nama Perusahaan --}}
                <x-label-req>{{ __('Nama Perusahaan') }} </x-label-req>
                <x-text-input wire:model.live='company_name' :error="$errors->get('company_name')" type="text" placeholder="Nama Perusahaan" />
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
                <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close</flux:button>
            </div>
        </form>
    </flux:modal>
    <flux:modal name="delete-bu" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete project?</flux:heading>
                <flux:text class="mt-2">
                    <p>You're about to delete this {{ $bu_name }}.</p>
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
