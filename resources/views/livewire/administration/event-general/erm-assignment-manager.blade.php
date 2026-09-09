<section class="w-full">
    <x-toast />
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
    @include('partials.event-general-head')
    @push('styles')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    @endpush
    <!-- name of each tab group should be unique -->
    <x-tabs-event.layout>
        <div x-data x-on:open-my-modal.window="my_modal_1.showModal()" x-on:close-my-modal.window="my_modal_1.close()">
            <dialog id="my_modal_1" class="modal" wire:ignore.self>
                <div class="w-11/12 md:max-w-3xl modal-box">
                    <h3 class="text-lg font-bold">Tambah User</h3>
                    <div class="grid grid-cols-1 gap-2 md:grid-cols-3 lg:grid-cols-3">
                        <fieldset class="fieldset ">
                            <label class="block">Pilih ERM</label>
                            <div class="relative">
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="searchModerator"
                                        placeholder="Ketik untuk mencari dan memilih ERM..." {{-- 💡 Terapkan SEMUA class styling ke input --}}
                                        class="w-full pr-10 input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />

                                    {{-- Spinner diposisikan absolute di kanan input --}}
                                    <div wire:loading.remove.class='hidden' wire:target="searchModerator,selectModerator"
                                        class="absolute inset-y-0 right-0 z-10 flex items-center hidden pr-3 pointer-events-none">
                                        <span class="loading loading-spinner loading-sm text-secondary"></span>
                                    </div>
                                </div>

                                @if ($showModeratorDropdown && count($users) > 0)
                                <ul
                                    class="absolute z-10 w-full mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60">
                                    @foreach ($users as $user)
                                    <li wire:click="selectModerator({{ $user->id }}, '{{ $user->name }}')"
                                        class="px-3 py-2 cursor-pointer hover:bg-base-200" wire:loading.attr="disabled">
                                        {{ $user->name }}
                                    </li>
                                    @endforeach
                                </ul>
                                @endif

                                @foreach ($moderator_ids as $id)
                                <input type="hidden" name="moderator_ids[]" value="{{ $id }}">
                                @endforeach

                            </div>
                            <x-label-error :messages="$errors->get('moderator_ids')" />
                            @if (count($selectedModerators) > 0)
                            <div class="flex flex-wrap gap-2 mt-2 mb-3">
                                @foreach ($selectedModerators as $moderator)
                                <div class="gap-2 badge badge-xs badge-info">
                                    <span>{{ $moderator['name'] }}</span>
                                    <button type="button" wire:click="removeModerator({{ $moderator['id'] }})"
                                        class="btn btn-xs btn-circle btn-ghost">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </fieldset>

                        <fieldset>
                            <input id="department" value="department" wire:model="status_new"
                                class="peer/department radio radio-xs radio-accent" type="radio" name="status_new" checked="checked" />
                            <label for="department" class="peer-checked/department:text-accent">Departemen</label>

                            <input id="company" value="company" wire:model="status_new"
                                class="peer/company radio radio-xs radio-primary" type="radio" name="status_new" />
                            <label for="company" class="peer-checked/company:text-primary">Kontraktor</label>

                            <div class="hidden peer-checked/department:block mt-0.5">
                                {{-- Department --}}
                                <div class="relative mb-1">
                                    <!-- Input Search -->
                                    <div class="relative">
                                        <input type="text" wire:model.live.debounce.300ms="searchDepartemen"
                                            placeholder="Cari departemen..." {{-- 💡 Terapkan SEMUA class styling ke input --}}
                                            class="w-full pr-10 input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />
                                        {{-- Spinner diposisikan absolute di kanan input --}}
                                        <div wire:loading.remove.class='hidden' wire:target="searchDepartemen,selectDepartment"
                                            class="absolute inset-y-0 right-0 z-10 flex items-center hidden pr-3 pointer-events-none">
                                            <span class="loading loading-spinner loading-sm text-secondary"></span>
                                        </div>
                                    </div>
                                    <!-- Dropdown hasil search -->
                                    @if ($showDepartemenDropdown && count($departments) > 0)
                                    <ul
                                        class="absolute z-10 w-full mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60">
                                        @foreach ($departments as $dept)
                                        <li wire:click="selectDepartment({{ $dept->id }}, '{{ $dept->department_name }}')"
                                            class="px-3 py-2 cursor-pointer hover:bg-base-200">
                                            {{ $dept->department_name }}
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
                                </div>
                                @if ($status === 'department')
                                <x-label-error :messages="$errors->get('department_id')" />
                                @endif
                            </div>
                            <div class="hidden peer-checked/company:block mt-0.5">
                                {{-- Contractor --}}
                                <div class="relative mb-1">
                                    <!-- Input Search -->
                                    <div class="relative">
                                        <input type="text" wire:model.live.debounce.300ms="searchContractor"
                                            placeholder="Cari departemen..." {{-- 💡 Terapkan SEMUA class styling ke input --}}
                                            class="w-full pr-10 input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />
                                        {{-- Spinner diposisikan absolute di kanan input --}}
                                        <div wire:loading.remove.class='hidden' wire:target="searchContractor,selectContractor"
                                            class="absolute inset-y-0 right-0 z-10 flex items-center hidden pr-3 pointer-events-none">
                                            <span class="loading loading-spinner loading-sm text-secondary"></span>
                                        </div>
                                        <!-- Dropdown hasil search -->
                                        @if ($showContractorDropdown && count($contractors) > 0)
                                        <ul
                                            class="absolute z-10 w-full mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60">
                                            <!-- Spinner ketika klik -->
                                            @foreach ($contractors as $contractor)
                                            <li wire:click="selectContractor({{ $contractor->id }}, '{{ $contractor->contractor_name }}')"
                                                class="px-3 py-2 cursor-pointer hover:bg-base-200">
                                                {{ $contractor->contractor_name }}
                                            </li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </div>
                                    @if ($status === 'company')
                                    <x-label-error :messages="$errors->get('contractor_id')" />
                                    @endif
                                </div>
                        </fieldset>
                    </div>
                    <div class="modal-action">
                        <div class="flex gap-2 mt-2">
                            <flux:button size="xs" wire:click="close_modal" icon:trailing="close-icon" onclick="my_modal_1.close()" variant="danger">
                                Batal
                            </flux:button>
                            <flux:button size="xs" wire:click="assign" icon:trailing="save-icon" variant="primary">
                                Simpan
                            </flux:button>
                        </div>
                    </div>
                </div>
            </dialog>
        </div>
        <flux:button size="xs" variant="primary" wire:click="open_modal" icon:trailing="add-icon" onclick=""> Tambah ERM </flux:button>
        <hr class="my-4">
        <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
            <x-form.input-text label="Cari Nama ERM" model="search" placeholder="Cari nama ERM..." />
            <x-form.select
                label="department"
                model="department_search"
                :options="$department_all"
                optionValue="id"
                optionLabel="department_name"
                placeholder="-- Pilih Penanggung Jawab --" />
            <x-form.select
                label="Kontraktor"
                model="contractor_search"
                :options="$contractor_all"
                optionValue="id"
                optionLabel="contractor_name"
                placeholder="-- Pilih Penanggung Jawab --" />
        </div>
        <table class="w-full text-sm border table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-2 py-1 border">User</th>
                    <th class="px-2 py-1 border">Dept</th>
                    <th class="px-2 py-1 border">Contractor</th>
                    <th class="px-2 py-1 border">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($assignments as $mod)
                <tr>
                    <td class="px-2 border">{{ $mod->user->name }}</td>
                    <td class="px-2 border">{{ $mod->department->department_name ?? '-' }}</td>
                    <td class="px-2 border">{{ $mod->contractor->contractor_name ?? '-' }}</td>
                    <td class="px-2 border">
                        <flux:button size="xs" variant="warning" wire:click="edit({{ $mod->id }})">
                            Edit
                        </flux:button>
                        <button wire:click="delete({{ $mod->id }})"
                            class="text-xs text-red-500 hover:underline">
                            Hapus
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>


    </x-tabs-event.layout>

    @php
    if($editId)
    {
    $open = 'modal-open';
    }
    else
    {
    $open = '';
    }

    @endphp

    <div class="modal {{ $open }}">
        <div class="w-11/12 md:max-w-3xl modal-box">
            <h3 class="text-lg font-bold">Perbaharui User</h3>
            <div class="grid grid-cols-1 gap-2 md:grid-cols-3 lg:grid-cols-3">
                <fieldset class="fieldset ">
                    <label class="block">Pilih ERM</label>
                    <div class="relative">
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="searchModerator"
                                placeholder="Ketik untuk mencari dan memilih ERM..." {{-- 💡 Terapkan SEMUA class styling ke input --}}
                                class="w-full pr-10 input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />

                            {{-- Spinner diposisikan absolute di kanan input --}}
                            <div wire:loading.remove.class='hidden' wire:target="searchModerator,selectModerator"
                                class="absolute inset-y-0 right-0 z-10 flex items-center hidden pr-3 pointer-events-none">
                                <span class="loading loading-spinner loading-sm text-secondary"></span>
                            </div>
                        </div>

                        @if ($showModeratorDropdown && count($users) > 0)
                        <ul
                            class="absolute z-10 w-full mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60">
                            @foreach ($users as $user)
                            <li wire:click="selectModerator({{ $user->id }}, '{{ $user->name }}')"
                                class="px-3 py-2 cursor-pointer hover:bg-base-200" wire:loading.attr="disabled">
                                {{ $user->name }}
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    <x-label-error :messages="$errors->get('moderator_ids')" />
                </fieldset>

                <fieldset>
                    <input id="department" value="department" wire:model="status"
                        class="peer/department radio radio-xs radio-accent" type="radio" name="status" />
                    <label for="department" class="peer-checked/department:text-accent">Departemen</label>

                    <input id="company" value="company" wire:model="status"
                        class="peer/company radio radio-xs radio-primary" type="radio" name="status" />
                    <label for="company" class="peer-checked/company:text-primary">Kontraktor</label>

                    <div class="hidden peer-checked/department:block mt-0.5">
                        {{-- Department --}}
                        <div class="relative mb-1">
                            <!-- Input Search -->
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="searchDepartemen"
                                    placeholder="Cari departemen..." {{-- 💡 Terapkan SEMUA class styling ke input --}}
                                    class="w-full pr-10 input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />
                                {{-- Spinner diposisikan absolute di kanan input --}}
                                <div wire:loading.remove.class='hidden' wire:target="searchDepartemen,selectDepartment"
                                    class="absolute inset-y-0 right-0 z-10 flex items-center hidden pr-3 pointer-events-none">
                                    <span class="loading loading-spinner loading-sm text-secondary"></span>
                                </div>
                            </div>
                            <!-- Dropdown hasil search -->
                            @if ($showDepartemenDropdown && count($departments) > 0)
                            <ul
                                class="absolute z-10 w-full mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60">
                                @foreach ($departments as $dept)
                                <li wire:click="selectDepartment({{ $dept->id }}, '{{ $dept->department_name }}')"
                                    class="px-3 py-2 cursor-pointer hover:bg-base-200">
                                    {{ $dept->department_name }}
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        @if ($status === 'department')
                        <x-label-error :messages="$errors->get('department_id')" />
                        @endif
                    </div>
                    <div class="hidden peer-checked/company:block mt-0.5">
                        {{-- Contractor --}}
                        <div class="relative mb-1">
                            <!-- Input Search -->
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="searchContractor"
                                    placeholder="Cari Kontractor..." {{-- 💡 Terapkan SEMUA class styling ke input --}}
                                    class="w-full pr-10 input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />
                                {{-- Spinner diposisikan absolute di kanan input --}}
                                <div wire:loading.remove.class='hidden' wire:target="searchContractor,selectContractor"
                                    class="absolute inset-y-0 right-0 z-10 flex items-center hidden pr-3 pointer-events-none">
                                    <span class="loading loading-spinner loading-sm text-secondary"></span>
                                </div>
                                <!-- Dropdown hasil search -->
                                @if ($showContractorDropdown && count($contractors) > 0)
                                <ul
                                    class="absolute z-10 w-full mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60">
                                    <!-- Spinner ketika klik -->
                                    @foreach ($contractors as $contractor)
                                    <li wire:click="selectContractor({{ $contractor->id }}, '{{ $contractor->contractor_name }}')"
                                        class="px-3 py-2 cursor-pointer hover:bg-base-200">
                                        {{ $contractor->contractor_name }}
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </div>
                            @if ($status === 'company')
                            <x-label-error :messages="$errors->get('contractor_id')" />
                            @endif
                        </div>
                </fieldset>
            </div>
            <div class="modal-action">
                <div class="flex gap-2 mt-2">
                    <flux:button size="xs" wire:click="close_modal_update" icon:trailing="close-icon" onclick="my_modal_2.close()" variant="danger">
                        Batal
                    </flux:button>
                    <flux:button size="xs" wire:click="update" icon:trailing="save-icon" variant="primary">
                        Simpan
                    </flux:button>
                </div>
            </div>
        </div>
    </div>

</section>
