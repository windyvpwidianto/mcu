<section class="w-full">
    <x-toast />
    @include('partials.menu')
    <x-tabs-menu.layout>
        <div class="flex justify-between">
            <flux:tooltip content="tambah data" position="top">
                <flux:button size="xs" wire:click='open_modal' icon="add-icon" variant="primary"></flux:button>
            </flux:tooltip>
        </div>

        <div class="overflow-x-auto ">
            <table class="table table-xs">
                <thead class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Icon') }}</th>
                        <th>{{ __('Nama Menu') }}</th>
                        <th>{{ __('Menu Utama') }}</th>
                        <th>{{ __('Route') }}</th>
                        <th>{{ __('Request Route') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Urutan') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach ($ExtraSubMenu as $no => $menu)
                    <tr>
                        <th>{{ $ExtraSubMenu->firstItem() + $no }}</th>
                        <th>
                            <flux:button size="xs" icon="{{ $menu->icon }}" variant="ghost">{{ $menu->icon }}</flux:button>
                        </th>
                        <th>{{ $menu->menu }}</th>
                        <th>{{ $menu->SubMenu->menu }}</th>
                        <th>{{ $menu->route }}</th>
                        <th>{{ $menu->request_route }}</th>
                        <th>{{ $menu->status }}</th>
                        <th>{{ $menu->urutan }}</th>
                        <th class='flex justify-center flex-row gap-2'>
                            <flux:tooltip content="edit" position="top">
                                <flux:button wire:click="open_modal_edit({{ $menu->id }})" size="xs" icon="pencil-square" variant="subtle"></flux:button>
                            </flux:tooltip>
                            <flux:tooltip content="hapus" position="top">
                                <flux:button wire:click="delete({{ $menu->id }})" wire:confirm.prompt="Are you sure you want to delete menu ?\n\nType DELETE to confirm|DELETE" size="xs" icon="trash" variant="danger"></flux:button>
                            </flux:tooltip>
                        </th>
                    </tr>
                    @endforeach

                </tbody>
                <tfoot class="text-center">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Icon') }}</th>
                        <th>{{ __('Nama Menu') }}</th>
                        <th>{{ __('Menu Utama') }}</th>
                        <th>{{ __('Route') }}</th>
                        <th>{{ __('Request Route') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Urutan') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <flux:modal name="extra-sub-menu">
            <form wire:submit='store' class='grid justify-items-stretch'>
                @csrf
                <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm border p-2 sm:justify-self-center">
                    <legend class="fieldset-legend">{{ $legend }}</legend>
                    {{-- Menu --}}
                    <x-label-req>{{ __('nama menu') }} </x-label-req>
                    <x-text-input wire:model.live='menu' :error="$errors->get('menu')" type="text" placeholder="menu" />
                    <x-label-error :messages="$errors->get('menu')" />
                    {{-- Parent Menu --}}
                    <x-label-req>{{ __('Sub Menu') }} </x-label-req>
                    <flux:select size="xs" wire:model.live="submenu_id" placeholder="Choose Status...">
                        @foreach ($SubMenu as $menu)
                        <flux:select.option value="{{ $menu->id }}">{{ $menu->menu }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <x-label-error :messages="$errors->get('status')" />
                    {{-- Icon --}}
                    <x-label-req>{{ __('icon') }} </x-label-req>
                    <x-text-input wire:model.live='icon' :error="$errors->get('icon')" type="text" placeholder="icon" />
                    <x-label-error :messages="$errors->get('icon')" />
                    {{-- Route --}}
                    <x-label-req>{{ __('route') }} </x-label-req>
                    <x-text-input wire:model.live='route' :error="$errors->get('route')" type="text" placeholder="route" />
                    <x-label-error :messages="$errors->get('route')" />
                    {{-- Route --}}
                    <x-label-req>{{ __('request route') }} </x-label-req>
                    <x-text-input wire:model.live='request_route' :error="$errors->get('request_route')" type="text" placeholder="request_route" />
                    <x-label-error :messages="$errors->get('request_route')" />
                    {{-- Status --}}
                    <x-label-req>{{ __('Status') }} </x-label-req>
                    <flux:select size="xs" wire:model.live="status" placeholder="Choose Status...">
                        <flux:select.option value="enabled">enabled</flux:select.option>
                        <flux:select.option value="disabled">disabled</flux:select.option>
                    </flux:select>
                    <x-label-error :messages="$errors->get('status')" />
                    {{-- Urutan --}}
                    <x-label-req>{{ __('urutan') }} </x-label-req>
                    <x-text-input wire:model.live='urutan' :error="$errors->get('urutan')" type="text" placeholder="urutan" type='number' />
                    <x-label-error :messages="$errors->get('urutan')" />
                </fieldset>

                <div class="modal-action">
                    <flux:button size="xs" type="submit" icon="save-icon" variant="primary">Save</flux:button>
                    <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close</flux:button>
                </div>
            </form>
        </flux:modal>
        <flux:modal name="extra-sub-menu-edit">
            <form wire:submit='store' class='grid justify-items-stretch'>
                @csrf
                <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-xs sm:w-sm border p-2 sm:justify-self-center">
                    <legend class="fieldset-legend">{{ $legend }}</legend>
                    {{-- Menu --}}
                    <x-label-req>{{ __('nama menu') }} </x-label-req>
                    <x-text-input wire:model.live='menu' :error="$errors->get('menu')" type="text" placeholder="menu" />
                    <x-label-error :messages="$errors->get('menu')" />
                    {{-- Parent Menu --}}
                    <x-label-req>{{ __('Sub Menu') }} </x-label-req>
                    <flux:select size="xs" wire:model.live="submenu_id" placeholder="Choose Status...">
                        @foreach ($SubMenu as $menu)
                        <flux:select.option value="{{ $menu->id }}">{{ $menu->menu }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <x-label-error :messages="$errors->get('status')" />
                    {{-- Icon --}}
                    <x-label-req>{{ __('icon') }} </x-label-req>
                    <x-text-input wire:model.live='icon' :error="$errors->get('icon')" type="text" placeholder="icon" />
                    <x-label-error :messages="$errors->get('icon')" />
                    {{-- Route --}}
                    <x-label-req>{{ __('route') }} </x-label-req>
                    <x-text-input wire:model.live='route' :error="$errors->get('route')" type="text" placeholder="route" />
                    <x-label-error :messages="$errors->get('route')" />
                    {{-- Route --}}
                    <x-label-req>{{ __('request route') }} </x-label-req>
                    <x-text-input wire:model.live='request_route' :error="$errors->get('request_route')" type="text" placeholder="request_route" />
                    <x-label-error :messages="$errors->get('request_route')" />
                    {{-- Status --}}
                    <x-label-req>{{ __('Status') }} </x-label-req>
                    <flux:select size="xs" wire:model.live="status" placeholder="Choose Status...">
                        <flux:select.option value="enabled">enabled</flux:select.option>
                        <flux:select.option value="disabled">disabled</flux:select.option>
                    </flux:select>
                    <x-label-error :messages="$errors->get('status')" />
                    {{-- Urutan --}}
                    <x-label-req>{{ __('urutan') }} </x-label-req>
                    <x-text-input wire:model.live='urutan' :error="$errors->get('urutan')" type="text" placeholder="urutan" type='number' />
                    <x-label-error :messages="$errors->get('urutan')" />
                </fieldset>

                <div class="modal-action">
                    <flux:button size="xs" type="submit" icon="save-icon" variant="primary">Save</flux:button>
                    <flux:button size="xs" wire:click='close_modal' icon="close-icon" variant="danger">Close</flux:button>
                </div>
            </form>
        </flux:modal>
    </x-tabs-menu.layout>

</section>
