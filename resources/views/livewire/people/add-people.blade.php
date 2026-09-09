<section>
    <x-toast />

    <label class="btn btn-info btn-xs " x-data @click="$dispatch('open-my-modal')">Tambah Karyawan</label>

    <dialog id="my_modal_1" class="modal"
        x-data="{ open: false }"
        x-show="open"
        @open-my-modal.window="open = true; $el.showModal()"
        @close-my-modal.window="open = false; $el.close()"
        wire:ignore.self>

        <div class="modal-box">
            <h3 class="font-bold text-lg">Hello!</h3>


            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <!-- Name -->
                <x-form.input-text label="Nama Lengkap" model="first_name" placeholder="Masukkan nama lengkap"
                    required />
                <!-- Marga -->
                <x-form.input-text label="Marga" model="last_name" placeholder="Masukkan marga" required />
                <!-- Name yang di tampilkan -->
                <x-form.input-text label="Nama yang di tampilkan" model="name"
                    placeholder="Masukkan nama yang di tampilkan" required disabled />

                <!-- Email Address -->
                <x-form.input-text label="Alamat Email" model="email" placeholder="email@example.com" />
                <!-- Nomor ID -->
                <x-form.input-text label="Nomor ID" model="no_id" placeholder="Masukkan nomor ID" required />
                <!-- Jenis kelamin -->
                <x-form.select
                    label="Jenis Kelamin"
                    model="jenis_kelamin"
                    :options="['Laki-Laki' => 'Laki-Laki', 'Perempuan' => 'Perempuan']"
                    placeholder="-- Pilih Jenis Kelamin --"
                    required />
                <fieldset>
                    <input id="department" value="department" wire:model.live="status"
                        class="peer/department radio radio-xs radio-accent" type="radio" name="status" checked />
                    <label for="department" class="peer-checked/department:text-accent">Departemen @if ($status === 'department')
                        <span class="text-xs font-bold text-red-500">*</span>
                        @endif
                    </label>
                    <input id="company" value="company" wire:model.live="status"
                        class="peer/company radio radio-xs radio-primary" type="radio" name="status" />
                    <label for="company" class="peer-checked/company:text-primary">Kontraktor @if ($status === 'company')
                        <span class="text-xs font-bold text-red-500">*</span>
                        @endif
                    </label>

                    <div class="hidden peer-checked/department:block mt-0.5">
                        {{-- Department --}}
                        <div class="relative mb-1">
                            <!-- Input Search -->
                            <input type="text" wire:model.live.debounce.300ms="searchDepartemen"
                                placeholder="Department"
                                class="input input-xs focus-within:outline-none focus-within:border-info focus-within:ring-0" />
                            <!-- Dropdown hasil search -->
                            @if ($showDepartemenDropdown && count($departments) > 0)
                            <ul
                                class="absolute z-10 w-full mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60">
                                <!-- Spinner ketika klik salah satu -->
                                <div wire:loading wire:target="selectDepartment" class="p-2 text-center">
                                    <span class="loading loading-spinner loading-sm text-secondary"></span>
                                </div>
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
                        <x-label-error :messages="$errors->get('searchDepartemen')" />
                        @endif
                    </div>
                    <div class="hidden peer-checked/company:block mt-0.5">
                        {{-- Contractor --}}
                        <div class="relative mb-1">
                            <!-- Input Search -->
                            <input type="text" wire:model.live.debounce.300ms="searchContractor"
                                placeholder="Kontraktor"
                                class="input input-xs focus-within:outline-none focus-within:border-info focus-within:ring-0" />
                            <!-- Dropdown hasil search -->
                            @if ($showContractorDropdown && count($contractors) > 0)
                            <ul
                                class="absolute z-10 w-full mt-1 overflow-auto border rounded-md shadow bg-base-100 max-h-60">
                                <!-- Spinner ketika klik -->
                                <div wire:loading wire:target="selectContractor" class="p-2 text-center">
                                    <span class="loading loading-spinner loading-sm text-secondary"></span>
                                </div>
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
                        <x-label-error :messages="$errors->get('searchContractor')" />
                        @endif
                    </div>
                </fieldset>
            </div>
            <div class="modal-action">
                <flux:button icon="save-icon" wire:click="register" variant="primary" size="xs">
                    {{ __('Create account') }}
                </flux:button>
                <label @click="open = false; $el.closest('dialog').close()" class="btn btn-error btn-xs">{{ __('Close') }}</label>
            </div>
        </div>
    </dialog>
</section>
