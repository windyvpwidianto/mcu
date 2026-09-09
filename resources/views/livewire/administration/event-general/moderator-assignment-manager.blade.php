<section class="w-full">
    <x-toast />
    <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
    @include('partials.event-general-head')
    @push('styles')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    @endpush
    <!-- name of each tab group should be unique -->
    <x-tabs-event.layout>


        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-2">
            <fieldset class="fieldset ">
                <label class="block">Pilih Moderator</label>


                <div class="relative">
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="searchModerator"
                            placeholder="Ketik untuk mencari dan memilih Moderator..." {{-- 💡 Terapkan SEMUA class styling ke input --}}
                            class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs pr-10" />

                        {{-- Spinner diposisikan absolute di kanan input --}}
                        <div wire:loading.remove.class='hidden' wire:target="searchModerator,selectModerator"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 hidden">
                            <span class="loading loading-spinner loading-sm text-secondary"></span>
                        </div>
                    </div>
                    @if ($showModeratorDropdown && count($users) > 0)
                        <ul
                            class="absolute z-10 bg-base-100 border rounded-md w-full mt-1 max-h-60 overflow-auto shadow">
                            @forelse ($users as $user)
                                <li wire:click="selectModerator({{ $user->id }}, '{{ $user->name }}')"
                                    class="px-3 py-2 cursor-pointer hover:bg-base-200" wire:loading.attr="disabled">
                                    {{ $user->name }}
                                </li>
                            @empty
                                <li class="px-3 py-2 text-gray-500">Tidak ada hasil ditemukan.</li>
                            @endforelse
                        </ul>
                    @endif

                    @foreach ($moderator_ids as $id)
                        <input type="hidden" name="moderator_ids[]" value="{{ $id }}">
                    @endforeach

                </div>
                <x-label-error :messages="$errors->get('moderator_ids')" />
                @if (count($selectedModerators) > 0)
                    <div class="mt-2 mb-3 flex flex-wrap gap-2">
                        @foreach ($selectedModerators as $moderator)
                            <div class="badge badge-xs badge-info gap-2">
                                <span>{{ $moderator['name'] }}</span>
                                <button type="button" wire:click="removeModerator({{ $moderator['id'] }})"
                                    class="btn btn-xs btn-circle btn-ghost">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
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
                <input id="department" value="department" wire:model="status"
                    class="peer/department radio radio-xs radio-accent" type="radio" name="status" checked />
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
                                class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs pr-10" />
                            {{-- Spinner diposisikan absolute di kanan input --}}
                            <div wire:loading.remove.class='hidden' wire:target="searchDepartemen,selectDepartment"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 hidden">
                                <span class="loading loading-spinner loading-sm text-secondary"></span>
                            </div>
                        </div>
                        <!-- Dropdown hasil search -->
                        @if ($showDepartemenDropdown && count($departments) > 0)
                            <ul
                                class="absolute z-10 bg-base-100 border rounded-md w-full mt-1 max-h-60 overflow-auto shadow">
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
                                class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs pr-10" />
                            {{-- Spinner diposisikan absolute di kanan input --}}
                            <div wire:loading.remove.class='hidden' wire:target="searchContractor,selectContractor"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none z-10 hidden">
                                <span class="loading loading-spinner loading-sm text-secondary"></span>
                            </div>
                            <!-- Dropdown hasil search -->
                            @if ($showContractorDropdown && count($contractors) > 0)
                                <ul
                                    class="absolute z-10 bg-base-100 border rounded-md w-full mt-1 max-h-60 overflow-auto shadow">
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
            <fieldset class="fieldset">
                <x-form.label label="Tipe Bahaya" required />
                <select wire:model.live="event_type_id"
                    class="select select-xs select-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden {{ $errors->has('event_type_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}">
                    <option value="">-- Pilih --</option>
                    @foreach ($eventType as $co)
                        <option value="{{ $co->id }}">{{ $co->event_type_name }}</option>
                    @endforeach
                </select>
                <x-label-error :messages="$errors->get('event_type_id')" />
            </fieldset>
        </div>

        <div class="mt-2">
            <flux:button size="xs" wire:click="assign" icon:trailing="add-icon" variant="primary">
                Tambah Moderator
            </flux:button>
        </div>
        <hr class="my-4">
        <input type="text" wire:model.live="search" placeholder="Cari nama moderator..."
            class="px-3 py-1 border rounded text-sm w-1/2 mb-2">
        <table class="table-auto w-full text-sm border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2 py-1">User</th>
                    <th class="border px-2 py-1">Dept</th>
                    <th class="border px-2 py-1">Contractor</th>
                    <th class="border px-2 py-1">Tipe Bahaya</th>
                    <th class="border px-2 py-1">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($assignments as $mod)
                    <tr>
                        <td class="border px-2">{{ $mod->user->name }}</td>
                        <td class="border px-2">{{ $mod->department->department_name ?? '-' }}</td>
                        <td class="border px-2">{{ $mod->contractor->contractor_name ?? '-' }}</td>
                        <td class="border px-2">{{ $mod->eventType->event_type_name ?? '-' }}</td>
                        <td class="border px-2">
                            <button wire:click="delete({{ $mod->id }})"
                                class="text-red-500 hover:underline text-xs">
                                Hapus
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-tabs-event.layout>


</section>
