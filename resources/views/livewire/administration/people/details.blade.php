<section class="w-full">
    <x-toast />
    <x-tabs-people.layout :idUser="$userId">
        <div class="grid grid-cols-2 gap-4 mt-4">

            <fieldset class="fieldset">
                <x-form.label label="Nama" required />
                <input type="text" wire:model.live="name"
                    class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('name') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                <x-label-error :messages="$errors->get('name')" />
            </fieldset>

            <fieldset class="fieldset">
                <x-form.label label="Jenis Kelamin" required />
                <select wire:model.live="gender"
                    class="w-full select select-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs">
                    <option value="">-- Pilih --</option>
                    <option value="L">Laki - laki</option>
                    <option value="P">Perempuan</option>
                </select>
                <x-label-error :messages="$errors->get('gender')" />
            </fieldset>

            <fieldset class="fieldset">
                <x-form.label label="Tanggal Lahir" required />
                <input type="text" readonly id="date_birth" wire:model="date_birth"
                    class="w-full cursor-pointer input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs"
                    placeholder="Pilih tanggal lahir {{ $errors->has('date_birth') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}"
                    x-data="{ fp: null }" {{-- Tambahkan state untuk Flatpickr instance --}} x-init="// Inisialisasi Flatpickr dan simpan instance-nya
                    fp = flatpickr($refs.input, {
                        dateFormat: 'Y-m-d',
                    });

                    // Dengarkan event 'dateLoaded' dari Livewire
                    $wire.on('dateLoaded', () => {
                        // Set tanggal menggunakan nilai Livewire saat event dipanggil
                        if ($wire.date_birth) {
                            fp.setDate($wire.date_birth);
                        }
                    });" x-ref="input" />
                <x-label-error :messages="$errors->get('date_birth')" />
            </fieldset>

            <fieldset class="fieldset">
                <x-form.label label="Username" :required="!$userId" />
                <input type="text" wire:model.live="username"
                    class="w-full input input-bordered focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs" />
                <x-label-error :messages="$errors->get('username')" />
            </fieldset>

            <fieldset>
                <input id="department" value="department" wire:model="deptCont"
                    class="peer/department radio radio-xs radio-accent" type="radio" name="deptCont" checked />
                <x-form.label for="department" class="peer-checked/department:text-accent text-[10px]"
                    label="PT. MSM & PT. TTN" required />
                <input id="contractor" value="contractor" wire:model="deptCont"
                    class="peer/contractor radio radio-xs radio-primary" type="radio" name="deptCont" />
                <x-form.label for="contractor" class="peer-checked/contractor:text-primary" label="Kontraktor"
                    required />

                <div class="hidden peer-checked/department:block mt-0.5">
                    {{-- Department --}}
                    <div class="relative mb-1">
                        <!-- Input Search -->
                        <input name="search" type="text" wire:model.live.debounce.300ms="search"
                            wire:key="search-dept-{{ $userId }}" placeholder="Cari departemen..."
                            class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('department_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                        <!-- Dropdown hasil search -->
                        @if ($showDropdown && count($departments) > 0)
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
                    @if ($deptCont === 'department')
                        <x-label-error :messages="$errors->get('department_id')" />
                    @endif
                </div>
                <div class="hidden peer-checked/contractor:block mt-0.5">
                    {{-- Contractor --}}
                    <div class="relative mb-1">
                        <!-- Input Search -->
                        <input name="searchContractor" type="text" wire:model.live.debounce.300ms="searchContractor"
                            wire:key="search-contractor-{{ $userId }} placeholder="Cari kontraktor..."
                            class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('contractor_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
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
                    @if ($deptCont === 'contractor')
                        <x-label-error :messages="$errors->get('contractor_id')" />
                    @endif
                </div>
            </fieldset>

            <fieldset class="fieldset">
                <x-form.label label="Employee ID" required />
                <input type="text" wire:model.live="employee_id"
                    class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('employee_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                <x-label-error :messages="$errors->get('employee_id')" />
            </fieldset>

            <fieldset class="fieldset">
                <x-form.label label="Tanggal masuk" required />
                <input type="text" readonly id="date_commenced" wire:model="date_commenced"
                    class="cursor-pointer input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('date_commenced') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}"
                    placeholder="Pilih tanggal masuk" x-data="{ fp: null }" {{-- Tambahkan state untuk Flatpickr instance --}}
                    x-init="// Inisialisasi Flatpickr dan simpan instance-nya
                    fp = flatpickr($refs.input, {
                        dateFormat: 'Y-m-d',
                    });

                    // Dengarkan event 'dateLoaded' dari Livewire
                    $wire.on('dateLoaded', () => {
                        // Set tanggal menggunakan nilai Livewire saat event dipanggil
                        if ($wire.date_commenced) {
                            fp.setDate($wire.date_commenced);
                        }
                    });" x-ref="input" />
                <x-label-error :messages="$errors->get('date_commenced')" />
            </fieldset>

            <fieldset class="fieldset">
                <x-form.label label="Email" :required="!$userId" />
                <input type="email" wire:model.live="email"
                    class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('email') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                <x-label-error :messages="$errors->get('email')" />
            </fieldset>
            <fieldset class="fieldset">
                <x-form.label label="Pilih Peran" required />
                <select wire:model.live="role_id"
                    class="select select-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs{{ $errors->has('role_id') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}">
                    <option value="">-- Pilih --</option>
                    @foreach ($role as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                <x-label-error :messages="$errors->get('role_id')" />
            </fieldset>
            <fieldset class="fieldset">
                <x-form.label label="Password" required="{{ $userId ? false : true }}" />
                <input type="password" wire:model="password"
                    class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('password') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                {{-- Teks bantuan saat edit --}}
                @if ($userId)
                    <p class="text-[8px] text-gray-500 mt-0.5">Kosongkan jika tidak ingin mengubah password.
                    </p>
                @endif
                <x-label-error :messages="$errors->get('password')" />
            </fieldset>

            <fieldset class="fieldset">
                <x-form.label label="Konfirmasi Password" required="{{ $userId ? false : true }}" />
                <input type="password" wire:model="password_confirmation"
                    class="input input-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden input-xs {{ $errors->has('password_confirmation') ? 'ring-1 ring-rose-500 focus:ring-rose-500 focus:border-rose-500' : '' }}" />
                <x-label-error :messages="$errors->get('password_confirmation')" />
            </fieldset>

        </div>
        <div class="modal-action">
            <flux:button wire:click="save" size="xs" icon:trailing="save" variant="primary">
                {{ $userId ? 'Update' : 'Simpan' }}</flux:button>
        </div>
    </x-tabs-people.layout>
</section>
