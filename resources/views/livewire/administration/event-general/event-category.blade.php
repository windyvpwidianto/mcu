<section class="w-full">
    <x-toast />
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
    @include('partials.event-general-head')


    <!-- name of each tab group should be unique -->
      <x-tabs-event.layout>
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
                    <flux:input size='xs' icon="magnifying-glass" wire:model.live='search_event_category' placeholder="Search Event Category" />
                </div>
            </div>
                <div class="overflow-x-auto ">
                    <table class="table table-xs">
                        <thead class="text-center">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Nama Event Category') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @foreach ($EventCategory as $no => $event_category)
                            <tr>
                                <th>{{ $EventCategory->firstItem() + $no }}</th>
                                <th>{{ $event_category->event_category_name }}</th>
                                <th>{{ $event_category->status }}</th>
                                <th class='flex justify-center flex-row gap-2'>
                                    <flux:tooltip content="edit" position="top">
                                        <flux:button wire:click="open_modal_edit({{ $event_category->id }})" size="xs" icon="pencil-square" variant="subtle"></flux:button>
                                    </flux:tooltip>
                                    <flux:modal.trigger name="delete-company">
                                        <flux:tooltip content="hapus" position="top">
                                            <flux:button wire:click="showDelete({{ $event_category->id }})" size="xs" icon="trash" variant="danger"></flux:button>
                                        </flux:tooltip>
                                    </flux:modal.trigger>
                                </th>
                            </tr>
                            @endforeach

                        </tbody>
                        <tfoot class="text-center">
                            <tr>
                                <th>#</th>
                                <th>{{ __('Nama Event Category') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <div class="mt-4">{{ $EventCategory->links() }}</div>
            <flux:modal name="EventCategory">
                <form wire:submit='store' class='grid justify-items-stretch'>
                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm border p-4 sm:justify-self-center">
                        <legend class="fieldset-legend">{{ $legend }}</legend>
                        {{-- Nama Event Category --}}
                        <x-label-req>{{ __('Nama Event Category') }} </x-label-req>
                        <x-text-input wire:model.live='event_category_name' :error="$errors->get('event_category_name')" type="text" placeholder="Nama Event Category" />
                        <x-label-error :messages="$errors->get('event_category_name')" />
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
            <flux:modal name="EventCategoryEdit">
                <form wire:submit='store' class='grid justify-items-stretch'>
                    @csrf
                    <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm border p-4 sm:justify-self-center">
                        <legend class="fieldset-legend">{{ $legend }}</legend>
                        {{-- Nama Event Category --}}
                        <x-label-req>{{ __('Nama Event Category') }} </x-label-req>
                        <x-text-input wire:model.live='event_category_name' :error="$errors->get('event_category_name')" type="text" placeholder="Nama Event Category" />
                        <x-label-error :messages="$errors->get('event_category_name')" />
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
                    <fieldset wire:target="import,upload_data" wire:loading.class="skeleton" class="fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm border p-4 sm:justify-self-center">
                        <legend class="fieldset-legend">Upload Data</legend>
                        <flux:input size="xs" variant='outline' type="file" wire:model.live="upload_data" label="Upload Nama Event Category" />
                    </fieldset>

                    <div class="modal-action">
                        <flux:button size="xs" type="submit" wire:target="upload_data" wire:loading.class="btn btn-disabled" icon="save-icon" variant="primary">Save</flux:button>
                        <flux:button size="xs" wire:click='close_modal_upload' wire:target="upload_data" wire:loading.class="btn btn-disabled" icon="close-icon" variant="danger">Close</flux:button>
                    </div>
                </form>
            </flux:modal>
            <flux:modal name="delete-company" class="min-w-[22rem]">
                <div class="space-y-6">
                    <div>
                        <flux:heading size="lg">Delete project?</flux:heading>
                        <flux:text class="mt-2">
                            <p>You're about to delete this {{ $event_category_name }}.</p>
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

      </x-tabs-event.layout>


</section>
