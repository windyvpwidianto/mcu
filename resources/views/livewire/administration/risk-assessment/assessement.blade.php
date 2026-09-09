<section class="w-full">
    <x-toast />
    @include('partials.matrix')
    <x-tabs-manajemen-resiko.layout>
        <div class="flex justify-between">
            <div>
                <flux:tooltip content="tambah data" position="top">
                    <flux:button size="xs" wire:click='openModal' icon="add-icon" variant="primary"></flux:button>
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
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>Level</th>
                        <th>Hari Tindakan</th>
                        <th>Koordinator</th>
                        <th>Kewajiban Pelapor</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($risks as $no => $risk)
                    <tr class="text-center">
                        <th>{{ $risks->firstItem() + $no }}</th>
                        <td>{{ $risk->name }}</td>
                        <td>{{ $risk->action_days }}</td>
                        <td>{{ $risk->coordinator }}</td>
                        <td>{{ $risk->reporting_obligation }}</td>
                        <td>{{ $risk->notes }}</td>
                        <td>
                            <flux:tooltip content="edit" position="top">
                                <flux:button wire:click="edit_modal({{ $risk->id }})" size="xs" icon="pencil-square" variant="subtle"></flux:button>
                            </flux:tooltip>
                            <flux:tooltip content="hapus" position="top">
                                <flux:button wire:click="delete({{ $risk->id }})" wire:confirm.prompt="Are you sure you want to delete {{ $risk->name }} ?\n\nType DELETE to confirm|DELETE" size="xs" icon="trash" variant="danger"></flux:button>
                            </flux:tooltip>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <flux:modal name="Risk-Assessment">
            <form wire:submit.prevent="store" class='grid justify-items-stretch'>
                @csrf
                <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm sm:justify-self-center">
                    <legend class="fieldset-legend"></legend>
                    {{-- Name --}}
                    <x-label-req>{{ __('Level') }} </x-label-req>
                    <x-text-input wire:model.live='name' :error="$errors->get('name')" type="text" placeholder="Level" />
                    <x-label-error :messages="$errors->get('name')" />
                    {{-- Action Days --}}
                    <x-label-req>{{ __('Hari Tindakan') }} </x-label-req>
                    <x-text-input wire:model.live='action_days' :error="$errors->get('action_days')" type="text" placeholder="Hari Tindakan" />
                    <x-label-error :messages="$errors->get('action_days')" />
                    {{-- Coordinator --}}
                    <x-label-req>{{ __('Koordinator') }} </x-label-req>
                    <x-text-input wire:model.live='coordinator' :error="$errors->get('coordinator')" type="text" placeholder="coordinator" />
                    <x-label-error :messages="$errors->get('coordinator')" />
                    {{-- Reporting Obligation --}}
                    <x-label-req>{{ __('Kewajiban Pelapor') }} </x-label-req>
                    <x-text-input wire:model.live='reporting_obligation' :error="$errors->get('reporting_obligation')" type="text" placeholder="Kewajiban Pelapor" />
                    <x-label-error :messages="$errors->get('reporting_obligation')" />
                    {{-- Notes --}}
                    <x-label-req>{{ __('Catatan') }} </x-label-req>
                    <x-text-area wire:model.live='notes' :error="$errors->get('notes')" type="text" placeholder="Catatan" />
                    <x-label-error :messages="$errors->get('notes')" />

                </fieldset>

                <div class="modal-action">
                    <flux:button size="xs" type="submit" icon="save-icon" variant="primary">
                       save</flux:button>
                    <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close
                    </flux:button>
                </div>
            </form>
        </flux:modal>
        <flux:modal name="Risk-AssessmentEdit">
            <form wire:submit.prevent="update" class='grid justify-items-stretch'>
                @csrf
                <fieldset class="p-4 border fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm sm:justify-self-center">
                    <legend class="fieldset-legend">Update Risk Consequence</legend>
                     {{-- Name --}}
                    <x-label-req>{{ __('Level') }} </x-label-req>
                    <x-text-input wire:model.live='name' :error="$errors->get('name')" type="text" placeholder="Level" />
                    <x-label-error :messages="$errors->get('name')" />
                    {{-- Action Days --}}
                    <x-label-req>{{ __('Hari Tindakan') }} </x-label-req>
                    <x-text-input wire:model.live='action_days' :error="$errors->get('action_days')" type="text" placeholder="Hari Tindakan" />
                    <x-label-error :messages="$errors->get('action_days')" />
                    {{-- Coordinator --}}
                    <x-label-req>{{ __('Koordinator') }} </x-label-req>
                    <x-text-input wire:model.live='coordinator' :error="$errors->get('coordinator')" type="text" placeholder="coordinator" />
                    <x-label-error :messages="$errors->get('coordinator')" />
                    {{-- Reporting Obligation --}}
                    <x-label-req>{{ __('Kewajiban Pelapor') }} </x-label-req>
                    <x-text-input wire:model.live='reporting_obligation' :error="$errors->get('reporting_obligation')" type="text" placeholder="Kewajiban Pelapor" />
                    <x-label-error :messages="$errors->get('reporting_obligation')" />
                    {{-- Notes --}}
                    <x-label-req>{{ __('Catatan') }} </x-label-req>
                    <x-text-area wire:model.live='notes' :error="$errors->get('notes')" type="text" placeholder="Catatan" />
                    <x-label-error :messages="$errors->get('notes')" />

                </fieldset>

                <div class="modal-action">
                    <flux:button size="xs" type="submit" icon="save-icon" variant="primary">
                      Update</flux:button>
                    <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close
                    </flux:button>
                </div>
            </form>
        </flux:modal>
    </x-tabs-manajemen-resiko.layout>
</section>
