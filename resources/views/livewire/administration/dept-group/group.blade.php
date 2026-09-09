<section class="w-full">
    <x-toast />
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
    {{-- @include('partials.dept-group-heading') --}}
    <x-tabs-group.layout :activeTab="$activeTab">

        <div class="overflow-x-auto ">
            <table class="table table-xs">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name Group') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($Groups as $no => $group)
                    <tr>
                        <th>{{ $Groups->firstItem() + $no }}</th>
                        <th>{{ $group->group_name }}</th>
                        <th class='flex justify-center flex-row gap-2'>
                            <flux:tooltip content="edit" position="top">
                                <flux:button wire:click="open_modal_edit({{ $group->id }})" size="xs" icon="pencil-square" variant="subtle"></flux:button>
                            </flux:tooltip>
                            <flux:tooltip content="hapus" position="top">
                                <flux:button wire:click="showDelete({{ $group->id }})" size="xs" icon="trash" variant="danger"></flux:button>
                            </flux:tooltip>
                        </th>
                    </tr>
                    @endforeach

                </tbody>
                <tfoot class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name Group') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-tabs-group.layout>
    <flux:modal name="group">
        <form wire:submit='store' class='grid justify-items-stretch'>
            @csrf
            <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm border p-2 sm:justify-self-center">
                <legend class="fieldset-legend">{{ $legend }}</legend>
                {{-- nama group --}}
                <x-label-req>{{ __('nama grup') }} </x-label-req>
                <x-text-input wire:model.live='group_name' :error="$errors->get('group_name')" type="text" placeholder="group_name" />
                <x-label-error :messages="$errors->get('group_name')" />
            </fieldset>

            <div class="modal-action">
                <flux:button size="xs" type="submit" icon="save-icon" variant="primary">Save</flux:button>
                <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close</flux:button>
            </div>
        </form>
    </flux:modal>
    <flux:modal name="group_edit">
        <form wire:submit='store' class='grid justify-items-stretch'>
            @csrf
            <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm border p-2 sm:justify-self-center">
                <legend class="fieldset-legend">{{ $legend }}</legend>
                {{-- nama group --}}
                <x-label-req>{{ __('nama grup') }} </x-label-req>
                <x-text-input wire:model.live='group_name' :error="$errors->get('group_name')" type="text" placeholder="group_name" />
                <x-label-error :messages="$errors->get('group_name')" />
            </fieldset>

            <div class="modal-action">
                <flux:button size="xs" type="submit" icon="save-icon" variant="primary">Save</flux:button>
                <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close</flux:button>
            </div>
        </form>
    </flux:modal>
    <flux:modal name="delete-group" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete project?</flux:heading>
                <flux:text class="mt-2">
                    <p>You're about to delete this {{ $group_name }}.</p>
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
